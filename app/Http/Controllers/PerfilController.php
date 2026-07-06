<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use App\Models\Pais;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PerfilController extends Controller
{
    /** Roles institucionales con permiso para editar datos completos de un usuario */
    private const ROLES_PRIVILEGIADOS = [
        'Departamento Personal',
        'Jefe de Personal',
        'Director Administrativo',
        'Administrador',
    ];

    public function show(): View
    {
        $usuario = auth()->user();
        $usuario->load([
            'designaciones'    => fn ($q) => $q->vigente()->with('cargo', 'institucion', 'dependencia'),
            'rolesInstitucion' => fn ($q) => $q->vigente()->with('rolInstitucion', 'institucion'),
            'ciudadDomicilio.estado',
            'estadoNacimiento',
            'paisNacimiento',
        ]);

        $paises          = Pais::orderBy('nombre')->get(['id', 'nombre']);
        $puedeEditarTodo = $this->puedeEditarTodo();

        $argentina   = Pais::where('iso2', 'AR')->value('id');
        $sanjuan     = $argentina ? Estado::where('id_pais', $argentina)->where('nombre', 'San Juan')->value('id') : null;

        return view('perfil.show', compact('usuario', 'puedeEditarTodo', 'paises', 'argentina', 'sanjuan'));
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario         = auth()->user();
        $puedeEditarTodo = $this->puedeEditarTodo();

        // Reglas base (contacto): disponibles para todos
        $rules = [
            'email'     => ['nullable', 'email', 'max:150', "unique:usuarios,email,{$usuario->id}"],
            'telefono'  => ['nullable', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
        ];

        // Departamento del domicilio (todos pueden)
        $rules['id_ciudad_domicilio'] = ['nullable', 'integer', 'exists:ciudades,id'];

        // Datos personales: solo para roles privilegiados
        if ($puedeEditarTodo) {
            $rules['apellidos']            = ['required', 'string', 'max:100'];
            $rules['nombres']              = ['required', 'string', 'max:100'];
            $rules['documento']            = ['required', 'string', 'max:20', "unique:usuarios,documento,{$usuario->id}"];
            $rules['sexo']                 = ['nullable', 'in:M,F,X'];
            $rules['id_pais_nacimiento']   = ['nullable', 'integer', 'exists:paises,id'];
            $rules['id_estado_nacimiento'] = ['nullable', 'integer', 'exists:estados,id'];
        }

        if ($request->hasFile('foto')) {
            $rules['foto'] = ['image', 'max:2048'];
        }
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }
        if ($request->filled('pin_marca')) {
            $rules['pin_marca'] = ['digits:4', 'confirmed'];
        }

        $data = $request->validate($rules);

        // Garantizar que solo se persistan los campos permitidos según el rol
        if (!$puedeEditarTodo) {
            $data = array_intersect_key($data, array_flip([
                'email', 'telefono', 'domicilio', 'id_ciudad_domicilio',
            ]));
        }

        // Foto de perfil (disponible para todos)
        if ($request->hasFile('foto')) {
            if ($usuario->foto) {
                Storage::disk('public')->delete($usuario->foto);
            }
            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }

        // Cambio de contraseña (disponible para todos)
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        // PIN de marca (disponible para todos — el cast 'hashed' lo hashea automáticamente)
        if ($request->filled('pin_marca')) {
            $data['pin_marca'] = $request->pin_marca;
        } else {
            unset($data['pin_marca']);
        }

        $usuario->update($data);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    private function puedeEditarTodo(): bool
    {
        $user   = auth()->user();
        $instId = (int) session('institucion_activa_id', 0);

        if ($user->permisos()->administrador()->tieneTodosLosPermisos()) {
            return true;
        }

        if ($instId) {
            foreach (self::ROLES_PRIVILEGIADOS as $rol) {
                if ($user->tieneRolEnInstitucion($rol, $instId)) {
                    return true;
                }
            }
        }

        return false;
    }
}
