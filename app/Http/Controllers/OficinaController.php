<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Oficina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OficinaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Oficina::with('edificio.institucion')->orderBy('nombre');

        if ($request->filled('edificio')) {
            $query->deEdificio((int) $request->edificio);
        }

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $oficinas  = $query->paginate(25)->withQueryString();
        $edificios = Edificio::activos()->with('institucion')->orderBy('nombre')->get();

        return view('oficinas.index', compact('oficinas', 'edificios'));
    }

    public function create(Request $request): View
    {
        $edificios       = Edificio::activos()->with('institucion')->orderBy('nombre')->get();
        $edificioSeleccionado = (int) $request->get('id_edificio', 0);
        return view('oficinas.create', compact('edificios', 'edificioSeleccionado'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        Oficina::create($data);
        return redirect()->route('oficinas.index')->with('success', 'Oficina creada.');
    }

    public function show(Oficina $oficina): View
    {
        $oficina->load('edificio.institucion');
        return view('oficinas.show', compact('oficina'));
    }

    public function edit(Oficina $oficina): View
    {
        $edificios = Edificio::activos()->with('institucion')->orderBy('nombre')->get();
        return view('oficinas.edit', compact('oficina', 'edificios'));
    }

    public function update(Request $request, Oficina $oficina): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        $oficina->update($data);
        return redirect()->route('oficinas.show', $oficina)->with('success', 'Oficina actualizada.');
    }

    public function destroy(Oficina $oficina): RedirectResponse
    {
        $oficina->delete();
        return redirect()->route('oficinas.index')->with('success', 'Oficina eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'id_edificio' => ['required', 'integer', 'exists:edificios,id'],
        ]);
    }
}
