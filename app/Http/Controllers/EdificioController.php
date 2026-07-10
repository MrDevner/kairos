<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EdificioController extends Controller
{
    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $query  = Edificio::with('institucion')->orderBy('nombre');

        if ($instId && !$request->filled('institucion')) {
            $query->deInstitucion($instId);
        } elseif ($request->filled('institucion')) {
            $query->deInstitucion((int) $request->institucion);
        }

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $edificios     = $query->paginate(25)->withQueryString();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('edificios.index', compact('edificios', 'instituciones'));
    }

    public function create(): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        return view('edificios.create', compact('instituciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        Edificio::create($data);
        return redirect()->route('edificios.index')->with('success', 'Edificio creado.');
    }

    public function show(Edificio $edificio): View
    {
        $edificio->load(['institucion', 'oficinas' => fn ($q) => $q->orderBy('nombre')]);
        return view('edificios.show', compact('edificio'));
    }

    public function edit(Edificio $edificio): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        return view('edificios.edit', compact('edificio', 'instituciones'));
    }

    public function update(Request $request, Edificio $edificio): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        $edificio->update($data);
        return redirect()->route('edificios.show', $edificio)->with('success', 'Edificio actualizado.');
    }

    public function destroy(Edificio $edificio): RedirectResponse
    {
        abort_if($edificio->oficinas()->exists(), 422, 'No se puede eliminar un edificio con oficinas/aulas cargadas.');
        $edificio->delete();
        return redirect()->route('edificios.index')->with('success', 'Edificio eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'         => ['required', 'string', 'max:255'],
            'descripcion'    => ['nullable', 'string'],
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id'],
        ]);
    }
}
