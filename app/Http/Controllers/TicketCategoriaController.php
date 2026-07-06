<?php

namespace App\Http\Controllers;

use App\Models\TicketCategoria;
use App\Permisos\Permisos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketCategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Permisos::delUsuarioActual()->administrador()->tieneTodosLosPermisos(), 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $categorias = TicketCategoria::withTrashed()->orderBy('nombre')->get();

        return view('tickets.categorias.index', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $slug = TicketCategoria::generarSlug($data['nombre']);

        if (TicketCategoria::withTrashed()->where('slug', $slug)->exists()) {
            return back()->withErrors(['nombre' => 'Ya existe una categoría con ese nombre (o uno muy similar), activa o eliminada.'])->withInput();
        }

        TicketCategoria::create(['nombre' => $data['nombre'], 'slug' => $slug, 'activo' => true]);
        TicketCategoria::limpiarCache();

        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, TicketCategoria $categoria): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'activo' => ['boolean'],
        ]);

        $slug = TicketCategoria::generarSlug($data['nombre']);

        if ($slug !== $categoria->slug
            && TicketCategoria::withTrashed()->where('slug', $slug)->where('id', '!=', $categoria->id)->exists()) {
            return back()->withErrors(['nombre' => 'Ya existe una categoría con ese nombre (o uno muy similar), activa o eliminada.'])->withInput();
        }

        $categoria->update([
            'nombre' => $data['nombre'],
            'slug'   => $slug,
            'activo' => $request->boolean('activo'),
        ]);
        TicketCategoria::limpiarCache();

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(TicketCategoria $categoria): RedirectResponse
    {
        $categoria->delete();
        TicketCategoria::limpiarCache();

        return back()->with('success', 'Categoría eliminada.');
    }

    public function restore(int $id): RedirectResponse
    {
        $categoria = TicketCategoria::onlyTrashed()->findOrFail($id);
        $categoria->restore();
        TicketCategoria::limpiarCache();

        return back()->with('success', 'Categoría restaurada.');
    }
}
