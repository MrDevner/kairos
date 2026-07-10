<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\CategoriaCargo;
use App\Models\Dependencia;
use App\Models\EventoCalendario;
use App\Models\Institucion;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $inicioMes = \Carbon\Carbon::create($anio, $mes, 1)->startOfMonth();
        $finMes    = $inicioMes->copy()->endOfMonth();

        $query = EventoCalendario::query()
            ->where('fecha_inicio', '<=', $finMes)
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $inicioMes))
            ->with('condiciones')
            ->orderBy('fecha_inicio');

        if ($instId) {
            $query->visiblesParaInstitucion($instId);
        } else {
            $query->whereNull('id_institucion'); // sin filtro de inst: solo generales
        }

        $eventos = $query->get();

        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('calendario.index', compact('eventos', 'instituciones', 'mes', 'anio', 'instId'));
    }

    public function create(Request $request): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $cargos        = Cargo::activos()->orderBy('nombre')->get();
        $dependencias  = Dependencia::orderBy('nombre')->get();
        $categorias    = CategoriaCargo::activas()->orderBy('nombre')->get();
        $fechaDefault  = $request->input('fecha_inicio', $request->input('fecha', now()->toDateString()));
        return view('calendario.create', compact('instituciones', 'cargos', 'dependencias', 'categorias', 'fechaDefault'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['afecta_computo'] = $request->boolean('afecta_computo');
        if ($request->alcance === 'general') {
            $data['id_institucion'] = null;
        }
        unset($data['alcance']);
        $evento = EventoCalendario::create($data);

        $this->sincronizarCondiciones($evento, $request);

        return redirect()->route('calendario.show', $evento)->with('success', 'Evento creado.');
    }

    public function show(EventoCalendario $calendario): View
    {
        $calendario->load(['institucion', 'condiciones']);
        return view('calendario.show', compact('calendario'));
    }

    public function edit(EventoCalendario $calendario): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $cargos        = Cargo::activos()->orderBy('nombre')->get();
        $dependencias  = Dependencia::orderBy('nombre')->get();
        $categorias    = CategoriaCargo::activas()->orderBy('nombre')->get();
        $calendario->load('condiciones');
        return view('calendario.edit', compact('calendario', 'instituciones', 'cargos', 'dependencias', 'categorias'));
    }

    public function update(Request $request, EventoCalendario $calendario): RedirectResponse
    {
        $data = $this->validar($request);
        $data['afecta_computo'] = $request->boolean('afecta_computo');
        if ($request->alcance === 'general') {
            $data['id_institucion'] = null;
        }
        unset($data['alcance']);
        $calendario->update($data);

        $this->sincronizarCondiciones($calendario, $request);

        return redirect()->route('calendario.show', $calendario)->with('success', 'Evento actualizado.');
    }

    public function destroy(EventoCalendario $calendario): RedirectResponse
    {
        $calendario->condiciones()->delete();
        $calendario->delete();
        return redirect()->route('calendario.index')->with('success', 'Evento eliminado.');
    }

    /**
     * Importa los feriados nacionales de un año desde la API pública ArgentinaDatos.
     */
    public function importarFeriados(Request $request): RedirectResponse
    {
        $anio = (int) $request->input('anio', now()->year);

        try {
            $response = Http::timeout(10)->get("https://api.argentinadatos.com/v1/feriados/{$anio}");
            $response->throw();
        } catch (ConnectionException $e) {
            return back()->with('error', 'No se pudo conectar con la API de feriados. Intente nuevamente más tarde.');
        } catch (RequestException $e) {
            return back()->with('error', 'La API de feriados respondió con un error para el año ' . $anio . '.');
        }

        $creados    = 0;
        $existentes = 0;

        foreach ($response->json() as $f) {
            $yaExiste = EventoCalendario::where('fecha_inicio', $f['fecha'])
                ->where('tipo', 'feriado')
                ->whereNull('id_institucion')
                ->exists();

            if ($yaExiste) {
                $existentes++;
                continue;
            }

            EventoCalendario::create([
                'id_institucion' => null,
                'titulo'         => $f['nombre'],
                'descripcion'    => 'Feriado ' . ($f['tipo'] ?? 'nacional') . ' — importado de ArgentinaDatos',
                'fecha_inicio'   => $f['fecha'],
                'tipo'           => 'feriado',
                'afecta_computo' => true,
            ]);
            $creados++;
        }

        $mensaje = "{$creados} feriados importados";
        if ($existentes) {
            $mensaje .= " ({$existentes} ya existían)";
        }
        $mensaje .= " para el año {$anio}.";

        return redirect()->route('calendario.index', ['anio' => $anio])->with('success', $mensaje);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'alcance'         => ['required', 'in:general,institucional'],
            'id_institucion'  => ['nullable', 'integer', 'exists:instituciones,id', 'required_if:alcance,institucional'],
            'titulo'          => ['required', 'string', 'max:255'],
            'descripcion'     => ['nullable', 'string'],
            'fecha_inicio'    => ['required', 'date'],
            'fecha_fin'       => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'tipo'            => ['required', 'in:feriado,suspension_total,suspension_parcial,evento_condicional,dia_no_laborable,paro'],
            'hora_desde'      => ['nullable', 'date_format:H:i', 'required_if:tipo,suspension_parcial'],
            'hora_hasta'      => ['nullable', 'date_format:H:i', 'after:hora_desde'],
            'afecta_computo'  => ['boolean'],
            'condiciones'                     => ['nullable', 'array'],
            'condiciones.*.tipo_condicion'    => ['required', 'string', 'max:50'],
            'condiciones.*.valor_condicion'   => ['required', 'string', 'max:255'],
            'condiciones.*.efecto'            => ['nullable', 'in:retiro_anticipado,ingreso_tardio,jornada_reducida,exencion'],
            'condiciones.*.minutos_afectados' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function sincronizarCondiciones(EventoCalendario $evento, Request $request): void
    {
        $evento->condiciones()->delete();

        $tiposConCondiciones = ['evento_condicional', 'paro'];

        if ($request->filled('condiciones') && in_array($request->tipo, $tiposConCondiciones)) {
            foreach ($request->condiciones as $c) {
                $evento->condiciones()->create([
                    'tipo_condicion'    => $c['tipo_condicion'],
                    'valor_condicion'   => $c['valor_condicion'],
                    'efecto'            => $c['efecto'] ?? null,
                    'minutos_afectados' => $c['minutos_afectados'] ?? null,
                ]);
            }
        }
    }
}
