<?php

namespace App\Http\Controllers;

use App\Models\Designacion;
use App\Models\Dispositivo;
use App\Models\ErrorServidor;
use App\Models\Institucion;
use App\Models\Licencia;
use App\Models\MarcaComputada;
use App\Models\MarcaOriginal;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

/**
 * Panel de administración (layout app2).
 *
 * Vista global del sistema para el Administrador General: no depende de la
 * institución activa en sesión. El acceso se restringe en la ruta mediante
 * el middleware EnsureAdministradorGeneral.
 */
class PanelController extends Controller
{
    public function index(): View
    {
        $hoy    = Carbon::today();
        $ayer   = $hoy->copy()->subDay();
        $hace30 = $hoy->copy()->subDays(29);

        // ── Indicadores principales ──────────────────────────────────────
        $marcasHoy  = MarcaOriginal::enFecha($hoy)->count();
        $marcasAyer = MarcaOriginal::enFecha($ayer)->count();

        $kpis = [
            'instituciones'       => Institucion::activas()->count(),
            'instituciones_total' => Institucion::count(),
            'usuarios'            => User::where('activo', true)->count(),
            'usuarios_nuevos_30d' => User::where('created_at', '>=', $hace30->copy()->startOfDay())->count(),
            'personal_vigente'    => Designacion::vigente()->count(),
            'dispositivos'        => Dispositivo::activos()->count(),
            'dispositivos_total'  => Dispositivo::count(),
            'marcas_hoy'          => $marcasHoy,
            'marcas_ayer'         => $marcasAyer,
            'marcas_delta_pct'    => $marcasAyer > 0
                ? (int) round(($marcasHoy - $marcasAyer) / $marcasAyer * 100)
                : null,
            'marcas_sin_procesar' => MarcaOriginal::noProcesadas()->count(),
        ];

        // ── Elementos que requieren atención ─────────────────────────────
        $atencion = [
            'licencias_pendientes' => Licencia::enEstado('pendiente')->count(),
            'tickets_abiertos'     => Ticket::whereIn('estado', Ticket::ESTADOS_ABIERTOS)->count(),
            'tickets_urgentes'     => Ticket::whereIn('estado', Ticket::ESTADOS_ABIERTOS)
                ->where('prioridad', 'urgente')->count(),
            'errores_activos'      => ErrorServidor::activos()->count(),
            'marcas_error_hoy'     => MarcaComputada::enFecha($hoy)->conErrores()->count(),
            'dispositivos_inactivos' => $kpis['dispositivos_total'] - $kpis['dispositivos'],
        ];

        // ── Serie de 30 días: marcas registradas y marcas con error ──────
        $marcasPorDia = MarcaOriginal::query()
            ->selectRaw('DATE(fecha_hora) as dia, COUNT(*) as total')
            ->whereBetween('fecha_hora', [$hace30->copy()->startOfDay(), $hoy->copy()->endOfDay()])
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $erroresPorDia = MarcaComputada::query()
            ->selectRaw('fecha as dia, COUNT(*) as total')
            ->conErrores()
            ->whereBetween('fecha', [$hace30->toDateString(), $hoy->toDateString()])
            ->groupBy('fecha')
            ->pluck('total', 'dia');

        $serie = ['labels' => [], 'marcas' => [], 'errores' => []];
        for ($i = 29; $i >= 0; $i--) {
            $fecha = $hoy->copy()->subDays($i);
            $clave = $fecha->toDateString();

            $serie['labels'][]  = $fecha->format('d/m');
            $serie['marcas'][]  = (int) ($marcasPorDia[$clave] ?? 0);
            $serie['errores'][] = (int) ($erroresPorDia[$clave] ?? 0);
        }

        // ── Listados operativos ──────────────────────────────────────────
        $licenciasPendientes = Licencia::enEstado('pendiente')
            ->with(['usuario', 'tipoLicencia', 'designacion.institucion'])
            ->orderBy('fecha_inicio')
            ->limit(6)
            ->get();

        $ticketsAbiertos = Ticket::whereIn('estado', Ticket::ESTADOS_ABIERTOS)
            ->with(['creador', 'asignadoA'])
            ->ordenParaListado()
            ->limit(6)
            ->get();

        $erroresRecientes = ErrorServidor::activos()
            ->with('usuario')
            ->orderByRaw('COALESCE(ultima_ocurrencia_en, created_at) DESC')
            ->limit(6)
            ->get();

        $actividadReciente = Activity::query()
            ->with('causer')
            ->latest()
            ->limit(8)
            ->get();

        // ── Instituciones: personal y dispositivos por institución ───────
        $personalPorInst = Designacion::vigente()
            ->selectRaw('id_institucion, COUNT(*) as total')
            ->groupBy('id_institucion')
            ->pluck('total', 'id_institucion');

        $dispositivosPorInst = Dispositivo::activos()
            ->selectRaw('id_institucion, COUNT(*) as total')
            ->groupBy('id_institucion')
            ->pluck('total', 'id_institucion');

        $instituciones = collect(Institucion::listaJerarquica())
            ->map(function (array $item) use ($personalPorInst, $dispositivosPorInst) {
                $inst = $item['institucion'];

                return [
                    'institucion'  => $inst,
                    'nivel'        => $item['nivel'],
                    'personal'     => (int) ($personalPorInst[$inst->id] ?? 0),
                    'dispositivos' => (int) ($dispositivosPorInst[$inst->id] ?? 0),
                    'hijas'        => $inst->hijasRecursivas->count(),
                ];
            });

        $personalTop = $instituciones
            ->filter(fn (array $i) => $i['personal'] > 0)
            ->sortByDesc('personal')
            ->take(6)
            ->map(fn (array $i) => [
                'label' => $i['institucion']->sigla ?: $i['institucion']->nombre,
                'total' => $i['personal'],
            ])
            ->values();

        return view('panel.index', [
            'hoy'                 => $hoy,
            'kpis'                => $kpis,
            'atencion'            => $atencion,
            'serie'               => $serie,
            'licenciasPendientes' => $licenciasPendientes,
            'ticketsAbiertos'     => $ticketsAbiertos,
            'erroresRecientes'    => $erroresRecientes,
            'actividadReciente'   => $actividadReciente,
            'instituciones'       => $instituciones,
            'institucionesTotal'  => $instituciones->count(),
            'personalTop'         => $personalTop,
        ]);
    }
}
