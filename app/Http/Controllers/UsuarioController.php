<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $query = Usuario::orderBy('apellidos');

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
        return view('usuarios.index', compact('usuarios'));
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
            'designaciones' => fn ($q) => $q->vigente()->with('cargo', 'institucion', 'dependencia'),
            'rolesInstitucion' => fn ($q) => $q->vigente()->with('rolInstitucion', 'institucion'),
        ]);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(Usuario $usuario): View
    {
        $roles = Role::orderBy('name')->get();
        return view('usuarios.edit', compact('usuario', 'roles'));
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
            'apellidos' => ['required', 'string', 'max:100'],
            'nombres'   => ['required', 'string', 'max:100'],
            'documento' => ['required', 'string', 'max:20', "unique:usuarios,documento,{$exceptId}"],
            'email'     => ['nullable', 'email', 'max:150', "unique:usuarios,email,{$exceptId}"],
            'sexo'      => ['nullable', 'in:M,F,X'],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
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
