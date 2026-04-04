<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\RolInstitucion;
use App\Models\RolInstitucionUsuario;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AsignacionRolController extends Controller
{
    // ── Roles institucionales ──────────────────────────────────────────────

    public function store(Request $request, Usuario $usuario): RedirectResponse
    {
        $actor  = $request->user();
        $instId = $this->instId();

        $nivelActor = $this->nivelActor($actor, $instId);
        $this->autorizarGestion($nivelActor);

        $data = $this->validarAsignacion($request);

        $rol = RolInstitucion::findOrFail($data['id_rol_institucion']);
        $this->verificarJerarquia($nivelActor, $rol);

        // No-Admin General solo puede asignar en su institución activa
        if (! $actor->hasRole('Administrador General')) {
            $data['id_institucion'] = $instId;
        }

        $existe = $usuario->rolesInstitucion()
            ->where('id_rol_institucion', $data['id_rol_institucion'])
            ->where('id_institucion', $data['id_institucion'])
            ->exists();

        if ($existe) {
            return back()->with('error', "El usuario ya tiene el rol «{$rol->nombre}» en esa institución.");
        }

        $data['activo'] = $request->boolean('activo');

        $usuario->rolesInstitucion()->create($data);

        return back()->with('success', "Rol «{$rol->nombre}» asignado correctamente.");
    }

    public function update(Request $request, RolInstitucionUsuario $asignacion): RedirectResponse
    {
        $actor  = $request->user();
        $instId = $this->instId();

        $nivelActor = $this->nivelActor($actor, $instId);
        $this->autorizarGestion($nivelActor);

        if (! $actor->hasRole('Administrador General') && $asignacion->id_institucion !== $instId) {
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

        if (! $actor->hasRole('Administrador General')) {
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

        if (! $actor->hasRole('Administrador General') && $asignacion->id_institucion !== $instId) {
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

    // ── Roles globales (solo Administrador General) ────────────────────────

    public function asignarGlobal(Request $request, Usuario $usuario): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Administrador General'), 403);

        $data = $request->validate([
            'rol' => ['required', 'string', 'exists:roles,name'],
        ]);

        $usuario->assignRole($data['rol']);

        return back()->with('success', "Rol global «{$data['rol']}» asignado.");
    }

    public function revocarGlobal(Request $request, Usuario $usuario): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Administrador General'), 403);

        $data = $request->validate([
            'rol' => ['required', 'string', 'exists:roles,name'],
        ]);

        $usuario->removeRole($data['rol']);

        return back()->with('success', "Rol global «{$data['rol']}» revocado.");
    }

    // ── Privados ───────────────────────────────────────────────────────────

    private function instId(): int
    {
        return (int) session('institucion_activa_id', 0);
    }

    /**
     * Devuelve el nivel jerárquico más alto (número menor) del actor.
     * El Administrador General obtiene nivel 0 (puede todo).
     */
    private function nivelActor(Usuario $actor, int $instId): int
    {
        if ($actor->hasRole('Administrador General')) {
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
