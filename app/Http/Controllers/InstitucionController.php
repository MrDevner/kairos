<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function index(): View
    {
        $arbol = Institucion::arbol();
        return view('instituciones.index', compact('arbol'));
    }

    public function create(): View
    {
        $padres = Institucion::activas()->orderBy('nombre')->get();
        return view('instituciones.create', compact('padres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['configuracion'] = $this->extraerConfig($request);

        Institucion::create($data);
        return redirect()->route('instituciones.index')->with('success', 'Institución creada.');
    }

    public function show(Institucion $institucion): View
    {
        $institucion->load(['padre', 'hijas', 'dependencias' => fn ($q) => $q->raices()->orderBy('nombre')]);
        return view('instituciones.show', compact('institucion'));
    }

    public function edit(Institucion $institucion): View
    {
        $padres = Institucion::activas()
            ->where('id', '!=', $institucion->id)
            ->whereNotIn('id', $institucion->idsConDescendientes())
            ->orderBy('nombre')->get();
        return view('instituciones.edit', compact('institucion', 'padres'));
    }

    public function update(Request $request, Institucion $institucion): RedirectResponse
    {
        $data = $this->validar($request);
        $data['configuracion'] = $this->extraerConfig($request);

        $institucion->update($data);
        return redirect()->route('instituciones.show', $institucion)->with('success', 'Institución actualizada.');
    }

    public function destroy(Institucion $institucion): RedirectResponse
    {
        abort_if($institucion->hijas()->exists(), 422, 'No se puede eliminar una institución con sub-instituciones.');
        $institucion->delete();
        return redirect()->route('instituciones.index')->with('success', 'Institución eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'sigla'                => ['nullable', 'string', 'max:20'],
            'descripcion'          => ['nullable', 'string'],
            'id_institucion_padre' => ['nullable', 'integer', 'exists:instituciones,id'],
            'direccion'            => ['nullable', 'string', 'max:255'],
            'telefono'             => ['nullable', 'string', 'max:30'],
            'email'                => ['nullable', 'email', 'max:150'],
            'activa'               => ['boolean'],
        ]);
    }

    private function extraerConfig(Request $request): array
    {
        return [
            'umbral_jornada_minima'   => (int) $request->input('cfg_umbral_jornada_minima', 60),
            'banco_horas_por'         => $request->input('cfg_banco_horas_por', 'usuario'),
            'permite_avisos_usuario'  => $request->boolean('cfg_permite_avisos_usuario'),
            'horas_extra_autorizadas' => $request->boolean('cfg_horas_extra_autorizadas'),
        ];
    }
}
