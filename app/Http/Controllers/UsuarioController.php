<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Pais;
use App\Models\RolInstitucion;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    /**
     * Búsqueda AJAX de usuarios para Select2.
     * Requiere al menos 3 caracteres. Retorna {results:[{id,text}]}.
     */
    public function buscar(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['results' => []]);
        }

        $results = Usuario::where('activo', true)
            ->where(fn ($query) =>
                $query->where('apellidos', 'like', "%{$q}%")
                      ->orWhere('nombres',   'like', "%{$q}%")
                      ->orWhere('documento', 'like', "%{$q}%")
            )
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->limit(25)
            ->get(['id', 'apellidos', 'nombres', 'documento'])
            ->map(fn ($u) => [
                'id'   => $u->id,
                'text' => $u->apellidos . ', ' . $u->nombres
                        . ($u->documento ? ' (' . $u->documento . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }

    public function index(Request $request): View
    {
        $instId   = (int) session('institucion_activa_id', 0);
        $esAdmin  = auth()->user()->hasRole('Administrador General');
        $verTodos = $esAdmin && $request->boolean('todos');
        $query    = Usuario::orderBy('apellidos');

        if ($instId && ! $verTodos) {
            $query->whereHas('designaciones', fn ($q) =>
                $q->vigente()->where('id_institucion', $instId)
            );
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('apellidos', 'like', "%{$b}%")
                  ->orWhere('nombres', 'like', "%{$b}%")
                  ->orWhere('documento', 'like', "%{$b}%")
                  ->orWhere('email', 'like', "%{$b}%");
            });
        }
        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->activo);
        }

        $usuarios = $query->paginate(25)->withQueryString();
        return view('usuarios.index', compact('usuarios', 'verTodos'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        $data = $this->manejarFoto($request, $data);
        $data = $this->manejarPassword($request, $data);

        $usuario = Usuario::create($data);

        if ($request->filled('roles')) {
            $usuario->syncRoles($request->roles);
        }

        return redirect()->route('usuarios.show', $usuario)->with('success', 'Usuario creado.');
    }

    public function show(Usuario $usuario): View
    {
        $usuario->load([
            'designaciones'       => fn ($q) => $q->vigente()->with('cargo', 'institucion', 'dependencia'),
            'rolesInstitucion'    => fn ($q) => $q->with('rolInstitucion', 'institucion')->orderBy('fecha_desde', 'desc'),
            'ciudadDomicilio.estado',
            'estadoNacimiento',
            'paisNacimiento',
        ]);

        $actor  = auth()->user();
        $instId = (int) session('institucion_activa_id', 0);

        $esAdminGeneral = $actor->hasRole('Administrador General');
        $nivelActor     = $esAdminGeneral ? 0 : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);
        $puedeGestionar = $esAdminGeneral || $nivelActor <= RolInstitucion::NIVEL_GESTION;

        $rolesInst           = null;
        $listaInstituciones  = null;
        $rolesGlobales       = null;
        $instActiva          = null;

        if ($puedeGestionar) {
            // Solo muestra roles con nivel estrictamente mayor al del actor (no puede asignar iguales o superiores)
            $rolesInst = RolInstitucion::where('activo', true)
                ->when(! $esAdminGeneral, fn ($q) => $q->where('nivel', '>', $nivelActor))
                ->orderBy('nivel')
                ->orderBy('nombre')
                ->get();

            if ($esAdminGeneral) {
                $listaInstituciones = Institucion::listaJerarquica();
                $rolesGlobales      = Role::orderBy('name')->get();
            } else {
                $instActiva = Institucion::find($instId);
            }
        }

        return view('usuarios.show', compact(
            'usuario', 'puedeGestionar', 'esAdminGeneral', 'nivelActor',
            'rolesInst', 'listaInstituciones', 'rolesGlobales', 'instActiva', 'instId'
        ));
    }

    public function edit(Usuario $usuario): View
    {
        $usuario->load('ciudadDomicilio.estado');
        $roles  = Role::orderBy('name')->get();
        $paises = Pais::orderBy('nombre')->get(['id', 'nombre']);
        return view('usuarios.edit', compact('usuario', 'roles', 'paises'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $data = $this->validar($request, $usuario->id);
        $data['activo'] = $request->boolean('activo');
        $data = $this->manejarFoto($request, $data, $usuario);
        $data = $this->manejarPassword($request, $data);

        $usuario->update($data);

        if ($request->filled('roles')) {
            $usuario->syncRoles($request->roles);
        }

        return redirect()->route('usuarios.show', $usuario)->with('success', 'Usuario actualizado.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        abort_if($usuario->id === auth()->id(), 422, 'No puede eliminar su propia cuenta.');
        $usuario->update(['activo' => false]);
        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado.');
    }

    private function validar(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'apellidos'            => ['required', 'string', 'max:100'],
            'nombres'              => ['required', 'string', 'max:100'],
            'documento'            => ['required', 'string', 'max:20', "unique:usuarios,documento,{$exceptId}"],
            'email'                => ['nullable', 'email', 'max:150', "unique:usuarios,email,{$exceptId}"],
            'telefono'             => ['nullable', 'string', 'max:30'],
            'domicilio'            => ['nullable', 'string', 'max:255'],
            'id_ciudad_domicilio'  => ['nullable', 'integer', 'exists:ciudades,id'],
            'id_pais_nacimiento'   => ['nullable', 'integer', 'exists:paises,id'],
            'id_estado_nacimiento' => ['nullable', 'integer', 'exists:estados,id'],
            'sexo'                 => ['nullable', 'in:M,F,X'],
            'password'             => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function manejarFoto(Request $request, array $data, ?Usuario $usuario = null): array
    {
        if ($request->hasFile('foto')) {
            $request->validate(['foto' => ['image', 'max:2048']]);
            if ($usuario?->foto) {
                Storage::disk('public')->delete($usuario->foto);
            }
            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }
        return $data;
    }

    private function manejarPassword(Request $request, array $data): array
    {
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        } else {
            unset($data['password']);
        }
        return $data;
    }
}
