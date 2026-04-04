<?php

namespace App\Http\Controllers;

use App\Models\RolInstitucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolInstitucionController extends Controller
{
    /** Módulos del sistema y su etiqueta legible. */
    public const MODULOS = [
        'instituciones' => 'Instituciones',
        'dependencias'  => 'Dependencias',
        'usuarios'      => 'Usuarios',
        'designaciones' => 'Designaciones',
        'ddjj'          => 'DDJJ',
        'licencias'     => 'Licencias',
        'marcas'        => 'Marcas',
        'banco_horas'   => 'Banco de horas',
        'informes'      => 'Informes',
        'calendario'    => 'Calendario',
        'avisos'        => 'Avisos',
        'dispositivos'  => 'Dispositivos',
        'roles'         => 'Roles y permisos',
    ];

    public function index(Request $request): View
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        $query = RolInstitucion::withCount('asignaciones')->orderBy('nivel')->orderBy('nombre');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('descripcion', 'like', "%{$b}%");
            });
        }

        $roles = $query->paginate(25)->withQueryString();

        return view('roles.index', compact('roles', 'nivelActor'));
    }

    public function create(): View
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        return view('roles.create', ['modulos' => self::MODULOS, 'nivelActor' => $nivelActor]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');

        // No puede crear un rol con nivel igual o menor al propio
        if ($nivelActor > 0 && $data['nivel'] <= $nivelActor) {
            return back()->withErrors(['nivel' => 'No puede crear un rol de igual o mayor jerarquía que la propia.'])->withInput();
        }

        $rol = RolInstitucion::create($data);

        $this->sincronizarPermisos($rol, $request);

        return redirect()->route('roles.show', $rol)
            ->with('success', 'Rol creado correctamente.');
    }

    public function show(RolInstitucion $role): View
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        $role->load([
            'permisos',
            'asignaciones' => fn ($q) => $q->with(['usuario', 'institucion'])->orderBy('fecha_desde', 'desc'),
        ]);

        $permisosIndexados = $role->permisos->keyBy('modulo');

        return view('roles.show', [
            'rol'               => $role,
            'modulos'           => self::MODULOS,
            'permisosIndexados' => $permisosIndexados,
            'nivelActor'        => $nivelActor,
        ]);
    }

    public function edit(RolInstitucion $role): View
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        // No puede editar un rol de igual o mayor jerarquía
        if ($nivelActor > 0 && $role->nivel <= $nivelActor) {
            abort(403, 'No puede editar un rol de igual o mayor jerarquía que la propia.');
        }

        $role->load('permisos');
        $permisosIndexados = $role->permisos->keyBy('modulo');

        return view('roles.edit', [
            'rol'               => $role,
            'modulos'           => self::MODULOS,
            'permisosIndexados' => $permisosIndexados,
            'nivelActor'        => $nivelActor,
        ]);
    }

    public function update(Request $request, RolInstitucion $role): RedirectResponse
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        if ($nivelActor > 0 && $role->nivel <= $nivelActor) {
            abort(403, 'No puede editar un rol de igual o mayor jerarquía que la propia.');
        }

        $data = $this->validar($request, $role->id);
        $data['activo'] = $request->boolean('activo');

        // Tampoco puede mover el rol a un nivel igual o menor al propio
        if ($nivelActor > 0 && $data['nivel'] <= $nivelActor) {
            return back()->withErrors(['nivel' => 'No puede asignar un nivel de igual o mayor jerarquía que la propia.'])->withInput();
        }

        $role->update($data);

        $this->sincronizarPermisos($role, $request);

        return redirect()->route('roles.show', $role)
            ->with('success', 'Rol actualizado.');
    }

    public function destroy(RolInstitucion $role): RedirectResponse
    {
        $actor      = auth()->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $actor->hasRole('Administrador General')
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        if ($nivelActor > 0 && $role->nivel <= $nivelActor) {
            abort(403, 'No puede eliminar un rol de igual o mayor jerarquía que la propia.');
        }

        if ($role->asignaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado.');
    }

    // ── Privados ───────────────────────────────────────────────────────────

    private function validar(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'nombre'      => ['required', 'string', 'max:255', 'unique:roles_institucion,nombre' . ($exceptId ? ",{$exceptId}" : '')],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo'      => ['boolean'],
            'nivel'       => ['required', 'integer', 'min:1', 'max:999'],
        ]);
    }

    private function sincronizarPermisos(RolInstitucion $rol, Request $request): void
    {
        foreach (array_keys(self::MODULOS) as $modulo) {
            $permisos = $request->input("permisos.{$modulo}", []);

            $rol->permisos()->updateOrCreate(
                ['modulo' => $modulo],
                [
                    'puede_ver'      => in_array('ver', $permisos),
                    'puede_crear'    => in_array('crear', $permisos),
                    'puede_editar'   => in_array('editar', $permisos),
                    'puede_eliminar' => in_array('eliminar', $permisos),
                ]
            );
        }
    }
}
