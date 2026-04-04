<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\CategoriaCargo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CargoController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            auth()->user()->hasRole('Administrador General') || session('institucion_activa_id'),
            403
        );

        $query = Cargo::with('categoria')->orderBy('nombre');

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }
        if ($request->filled('categoria')) {
            $query->where('id_categoria', (int) $request->categoria);
        }
        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->activo);
        }

        $cargos     = $query->paginate(40)->withQueryString();
        $categorias = CategoriaCargo::orderBy('nombre')->get();

        return view('cargos.index', compact('cargos', 'categorias'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $categorias = CategoriaCargo::activas()->orderBy('nombre')->get();
        return view('cargos.create', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        Cargo::create($data);

        return redirect()->route('cargos.index')->with('success', 'Cargo creado.');
    }

    public function edit(Cargo $cargo): View
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $categorias = CategoriaCargo::activas()->orderBy('nombre')->get();
        return view('cargos.edit', compact('cargo', 'categorias'));
    }

    public function update(Request $request, Cargo $cargo): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        $cargo->update($data);

        return redirect()->route('cargos.index')->with('success', 'Cargo actualizado.');
    }

    public function destroy(Cargo $cargo): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        if ($cargo->designaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay designaciones asociadas a este cargo.');
        }
        $cargo->delete();

        return redirect()->route('cargos.index')->with('success', 'Cargo eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'          => ['required', 'string', 'max:255'],
            'horas_semanales' => ['required', 'numeric', 'min:0', 'max:168'],
            'indice'          => ['nullable', 'numeric', 'min:0', 'max:99'],
            'id_categoria'    => ['nullable', 'integer', 'exists:categorias_cargo,id'],
        ]);
    }
}
