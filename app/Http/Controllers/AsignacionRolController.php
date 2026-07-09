<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\RolInstitucion;
use App\Models\RolInstitucionUsuario;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AsignacionRolController extends Controller
{
    // ── Roles institucionales ──────────────────────────────────────────────

    public function store(Request $request, User $usuario): RedirectResponse
    {
        $actor  = $request->user();
        $instId = $this->instId();

        $nivelActor = $this->nivelActor($actor, $instId);
        $this->autorizarGestion($nivelActor);

        $data = $this->validarAsignacion($request);

        $rol = RolInstitucion::findOrFail($data['id_rol_institucion']);
        $this->verificarJerarquia($nivelActor, $rol);

        // No-Admin General solo puede asignar en su institución activa
        if (! $this->esAdminGeneral($actor)) {
            $data['id_institucion'] = $instId;
        }

        $existe = $usuario->rolesInstitucion()
            ->where('id_rol_institucion', $data['id_rol_institucion'])
            ->where('id_institucion', $data['id_institucion'])
            ->exists();

        if ($existe) {
            return back()->with('error', "El usuario ya tiene el rol «{$rol->nombre}» en esa institución.");
        }

        $data['activo']          = $request->boolean('activo');
        $data['id_asignado_por'] = $actor->id;

        $usuario->rolesInstitucion()->create($data);

        return back()->with('success', "Rol «{$rol->nombre}» asignado correctamente.");
    }

    public function update(Request $request, RolInstitucionUsuario $asignacion): RedirectResponse
    {
        $actor  = $request->user();
        $instId = $this->instId();

        $nivelActor = $this->nivelActor($actor, $instId);
        $this->autorizarGestion($nivelActor);

        if (! $this->esAdminGeneral($actor) && $asignacion->id_institucion !== $instId) {
            abort(403, 'Solo puede gestionar roles de la institución activa.');
        }

        $data = $this->validarAsignacion($request);

        $rolNuevo   = RolInstitucion::findOrFail($data['id_rol_institucion']);
        $rolActual  = $asignacion->rolInstitucion;

        // Verificar jerarquía tanto para el rol actual como para el nuevo
        $this->verificarJerarquia($nivelActor, $rolNuevo);
        if ($rolActual) {
            $this->verificarJerarquia($nivelActor, $rolActual);
        }

        if (! $this->esAdminGeneral($actor)) {
            $data['id_institucion'] = $asignacion->id_institucion;
        }

        $data['activo'] = $request->boolean('activo');
        $asignacion->update($data);

        return back()->with('success', 'Asignación de rol actualizada.');
    }

    public function destroy(RolInstitucionUsuario $asignacion): RedirectResponse
    {
        $actor  = auth()->user();
        $instId = $this->instId();

        $nivelActor = $this->nivelActor($actor, $instId);
        $this->autorizarGestion($nivelActor);

        if (! $this->esAdminGeneral($actor) && $asignacion->id_institucion !== $instId) {
            abort(403, 'Solo puede gestionar roles de la institución activa.');
        }

        if ($asignacion->rolInstitucion) {
            $this->verificarJerarquia($nivelActor, $asignacion->rolInstitucion);
        }

        $usuarioId = $asignacion->id_usuario;
        $rolNombre = $asignacion->rolInstitucion?->nombre ?? 'Rol';

        $asignacion->delete();

        return redirect()->route('usuarios.show', $usuarioId)
            ->with('success', "Rol «{$rolNombre}» revocado.");
    }

    // ── Roles globales (solo Administrador General, vía comodín "*") ───────

    public function asignarGlobal(Request $request, User $usuario): RedirectResponse
    {
        abort_unless($this->esAdminGeneral($request->user()), 403);

        $data = $request->validate([
            'rol' => ['required', 'string'],
        ]);

        $rol = $this->rolesGlobales()->firstWhere('nombre', $data['rol']);
        abort_unless($rol, 422, 'Rol global inválido.');

        $usuario->rolesInstitucion()->firstOrCreate(
            ['id_rol_institucion' => $rol->id, 'id_institucion' => null],
            ['activo' => true, 'fecha_desde' => now()->toDateString(), 'id_asignado_por' => $request->user()->id]
        );

        return back()->with('success', "Rol global «{$rol->nombre}» asignado.");
    }

    public function revocarGlobal(Request $request, User $usuario): RedirectResponse
    {
        abort_unless($this->esAdminGeneral($request->user()), 403);

        $data = $request->validate([
            'rol' => ['required', 'string'],
        ]);

        $usuario->rolesInstitucion()
            ->whereNull('id_institucion')
            ->whereHas('rolInstitucion', fn ($q) => $q->where('nombre', $data['rol']))
            ->delete();

        return back()->with('success', "Rol global «{$data['rol']}» revocado.");
    }

    // ── Privados ───────────────────────────────────────────────────────────

    private function instId(): int
    {
        return (int) session('institucion_activa_id', 0);
    }

    private function esAdminGeneral(User $usuario): bool
    {
        return $usuario->permisos()->administrador()->tieneTodosLosPermisos();
    }

    /** Catálogo de roles asignables de forma global (los que tienen permiso comodín "*"). */
    private function rolesGlobales()
    {
        return RolInstitucion::whereHas('permisos', fn ($q) => $q->where('modulo', '*'))
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Devuelve el nivel jerárquico más alto (número menor) del actor.
     * El Administrador General obtiene nivel 0 (puede todo).
     */
    private function nivelActor(User $actor, int $instId): int
    {
        if ($this->esAdminGeneral($actor)) {
            return 0;
        }

        return RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);
    }

    /**
     * Verifica que el actor tenga permisos para gestionar roles (nivel <= NIVEL_GESTION).
     */
    private function autorizarGestion(int $nivelActor): void
    {
        if ($nivelActor > RolInstitucion::NIVEL_GESTION) {
            abort(403, 'No tiene permisos para gestionar roles de usuarios.');
        }
    }

    /**
     * Verifica que el rol objetivo tenga un nivel estrictamente mayor al del actor.
     * Nadie puede gestionar roles de igual o mayor autoridad que la propia.
     */
    private function verificarJerarquia(int $nivelActor, RolInstitucion $rol): void
    {
        if ($nivelActor === 0) {
            return; // Administrador General puede todo
        }

        if ($rol->nivel <= $nivelActor) {
            abort(403, "No puede gestionar el rol «{$rol->nombre}» porque es de igual o mayor jerarquía.");
        }
    }

    private function validarAsignacion(Request $request): array
    {
        return $request->validate([
            'id_rol_institucion' => ['required', 'integer', 'exists:roles_institucion,id'],
            'id_institucion'     => ['nullable', 'integer', 'exists:instituciones,id'],
            'fecha_desde'        => ['required', 'date'],
            'fecha_hasta'        => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'activo'             => ['boolean'],
        ]);
    }
}
