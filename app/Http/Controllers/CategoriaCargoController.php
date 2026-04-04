<?php

namespace App\Http\Controllers;

use App\Models\CategoriaCargo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaCargoController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $categorias = CategoriaCargo::withCount('cargos')->orderBy('nombre')->get();
        return view('categorias-cargo.index', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias_cargo,nombre'],
        ]);
        CategoriaCargo::create($data + ['activo' => true]);

        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, CategoriaCargo $categoriasCargo): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100',
                         "unique:categorias_cargo,nombre,{$categoriasCargo->id}"],
            'activo' => ['boolean'],
        ]);
        $data['activo'] = $request->boolean('activo');
        $categoriasCargo->update($data);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(CategoriaCargo $categoriasCargo): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Administrador General'), 403);

        if ($categoriasCargo->cargos()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay cargos asignados a esta categoría.');
        }
        $categoriasCargo->delete();

        return back()->with('success', 'Categoría eliminada.');
    }
}
