<?php

namespace App\Http\Controllers;

use App\Models\EventoCalendario;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function index(Request $request): View
    {
        $instId = $request->filled('institucion')
            ? (int) $request->institucion
            : (int) session('institucion_activa_id', 0);

        $mes  = $request->integer('mes',  now()->month);
        $anio = $request->integer('anio', now()->year);

        $eventos = EventoCalendario::deInstitucion($instId)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->with('condiciones')
            ->orderBy('fecha')
            ->get();

        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('calendario.index', compact('eventos', 'instituciones', 'mes', 'anio', 'instId'));
    }

    public function create(Request $request): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $fechaDefault  = $request->input('fecha', now()->toDateString());
        return view('calendario.create', compact('instituciones', 'fechaDefault'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $evento = EventoCalendario::create($data);

        if ($request->filled('condiciones')) {
            foreach ($request->condiciones as $c) {
                $evento->condiciones()->create($c);
            }
        }

        return redirect()->route('calendario.index')->with('success', 'Evento creado.');
    }

    public function show(EventoCalendario $calendario): View
    {
        $calendario->load(['institucion', 'condiciones']);
        return view('calendario.show', compact('calendario'));
    }

    public function edit(EventoCalendario $calendario): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $calendario->load('condiciones');
        return view('calendario.edit', compact('calendario', 'instituciones'));
    }

    public function update(Request $request, EventoCalendario $calendario): RedirectResponse
    {
        $data = $this->validar($request);
        $calendario->update($data);
        $calendario->condiciones()->delete();

        if ($request->filled('condiciones')) {
            foreach ($request->condiciones as $c) {
                $calendario->condiciones()->create($c);
            }
        }

        return redirect()->route('calendario.show', $calendario)->with('success', 'Evento actualizado.');
    }

    public function destroy(EventoCalendario $calendario): RedirectResponse
    {
        $calendario->condiciones()->delete();
        $calendario->delete();
        return redirect()->route('calendario.index')->with('success', 'Evento eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id'],
            'titulo'         => ['required', 'string', 'max:255'],
            'descripcion'    => ['nullable', 'string'],
            'fecha'          => ['required', 'date'],
            'tipo'           => ['required', 'in:feriado,suspension_total,suspension_parcial,evento_condicional,dia_no_laborable'],
            'hora_desde'     => ['nullable', 'date_format:H:i', 'required_if:tipo,suspension_parcial'],
            'hora_hasta'     => ['nullable', 'date_format:H:i', 'after:hora_desde'],
            'afecta_computo' => ['boolean'],
        ]);
    }
}
