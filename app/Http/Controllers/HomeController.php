<?php

namespace App\Http\Controllers;

use App\Models\Designacion;
use App\Models\Dispositivo;
use App\Models\ErrorServidor;
use App\Models\EventoCalendario;
use App\Models\Institucion;
use App\Models\ItemInforme;
use App\Models\Licencia;
use App\Models\MarcaComputada;
use App\Models\MarcaOriginal;
use App\Models\Ticket;
use App\Models\TipoLicencia;
use App\Models\User;
use App\Services\BancoHorasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Inicio.
     *  - Administrador General → panel global del sistema (home.admin).
     *  - Usuario con institución activa → dashboard institucional (home.institucion).
     *  - Sin institución → dashboard según rol (home.rol).
     */
    public function index(Request $request, BancoHorasService $bancoService): View
    {
        /** @var User $user */
        $user       = $request->user();
        $instId     = (int) session('institucion_activa_id', 0);
        $hoy        = Carbon::today();
        $instActiva = $instId > 0 ? Institucion::find($instId) : null;

        if ($user->permisos()->administrador()->tieneTodosLosPermisos()) {
            return view('home.admin', $this->datosPanel($hoy) + ['instActiva' => $instActiva]);
        }

        if ($instActiva) {
            return view('home.institucion', $this->datosInstitucion($instActiva, $user, $hoy));
        }

        if ($user->tieneRolEnInstitucion(['Jefe de Personal', 'Departamento Personal'], $instId)) {
            return view('home.rol', ['tipo' => 'personal', 'hoy' => $hoy] + $this->datosPersonal($instId, $hoy));
        }

        if ($user->tieneRolEnInstitucion(['Director Administrativo', 'Auditor'], $instId)) {
            return view('home.rol', ['tipo' => 'director', 'hoy' => $hoy] + $this->datosDirector($instId, $hoy));
        }

        return view('home.rol', ['tipo' => 'usuario', 'hoy' => $hoy] + $this->datosUsuario($user, $hoy, $bancoService));
    }

    /**
     * Dashboard de la institución activa (accesible para cualquier usuario con
     * institución seleccionada; el Administrador General llega desde el panel).
     */
    public function institucion(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $inst   = $instId > 0 ? Institucion::find($instId) : null;

        abort_unless($inst, 404, 'No hay institución activa seleccionada.');

        return view('home.institucion', $this->datosInstitucion($inst, $request->user(), Carbon::today()));
    }

    // ── Panel global (Administrador General) ──────────────────────────────

    private function datosPanel(Carbon $hoy): array
    {
        $ayer   = $hoy->copy()->subDay();
        $hace30 = $hoy->copy()->subDays(29);

        // Indicadores principales
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

        // Elementos que requieren atención
        $atencion = [
            'licencias_pendientes'   => Licencia::enEstado('pendiente')->count(),
            'tickets_abiertos'       => Ticket::whereIn('estado', Ticket::ESTADOS_ABIERTOS)->count(),
            'tickets_urgentes'       => Ticket::whereIn('estado', Ticket::ESTADOS_ABIERTOS)
                ->where('prioridad', 'urgente')->count(),
            'errores_activos'        => ErrorServidor::activos()->count(),
            'marcas_error_hoy'       => MarcaComputada::enFecha($hoy)->conErrores()->count(),
            'dispositivos_inactivos' => $kpis['dispositivos_total'] - $kpis['dispositivos'],
        ];

        // Serie de 30 días: marcas registradas y jornadas con error
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

        // Listados operativos
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

        // Instituciones: personal y dispositivos por institución
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

        return [
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
        ];
    }

    // ── Dashboard institucional ────────────────────────────────────────────

    private function datosInstitucion(Institucion $inst, User $user, Carbon $hoy): array
    {
        $instId = $inst->id;

        // Ruta jerárquica
        $ruta = $inst->rutaDesdeRaiz();

        // Estadísticas
        $personalVigente  = Designacion::vigente()->porInstitucion($instId)->count();
        $personalTotal    = Designacion::porInstitucion($instId)->count();
        $subInstituciones = $inst->hijas()->activas()->count();

        $licenciasPendientesCount = Licencia::where('estado', 'pendiente')
            ->whereHas('usuario.designaciones', fn ($q) => $q->vigente()->porInstitucion($instId))
            ->count();

        $ventana7 = $hoy->copy()->addDays(7);
        $eventosProximosCount = EventoCalendario::visiblesParaInstitucion($instId)
            ->where('fecha_inicio', '<=', $ventana7)
            ->where(fn ($q) => $q->where('fecha_inicio', '>=', $hoy)->orWhere('fecha_fin', '>=', $hoy))
            ->count();

        // Listados
        $licenciasPendientes = Licencia::where('estado', 'pendiente')
            ->whereHas('usuario.designaciones', fn ($q) => $q->vigente()->porInstitucion($instId))
            ->with(['usuario', 'tipoLicencia'])
            ->orderBy('fecha_inicio')
            ->limit(8)
            ->get();

        $ventana30 = $hoy->copy()->addDays(30);
        $eventosProximos = EventoCalendario::visiblesParaInstitucion($instId)
            ->where('fecha_inicio', '<=', $ventana30)
            ->where(fn ($q) => $q->where('fecha_inicio', '>=', $hoy)->orWhere('fecha_fin', '>=', $hoy))
            ->orderBy('fecha_inicio')
            ->limit(10)
            ->get();

        // Últimas licencias resueltas esta semana
        $licenciasRecientes = Licencia::whereIn('estado', ['aprobada', 'rechazada'])
            ->whereHas('usuario.designaciones', fn ($q) => $q->vigente()->porInstitucion($instId))
            ->with(['usuario', 'tipoLicencia'])
            ->where('fecha_aprobacion', '>=', $hoy->copy()->subDays(7))
            ->orderByDesc('fecha_aprobacion')
            ->limit(5)
            ->get();

        return [
            'instActiva'          => $inst,
            'instRuta'            => $ruta,
            'instStats'           => [
                'personal_vigente'     => $personalVigente,
                'personal_total'       => $personalTotal,
                'licencias_pendientes' => $licenciasPendientesCount,
                'eventos_proximos'     => $eventosProximosCount,
                'sub_instituciones'    => $subInstituciones,
                'tipos_licencia'       => TipoLicencia::activos()->visiblesParaInstitucion($instId)->count(),
            ],
            'licenciasPendientes' => $licenciasPendientes,
            'licenciasRecientes'  => $licenciasRecientes,
            'eventosProximos'     => $eventosProximos,
            'hoy'                 => $hoy,
        ];
    }

    // ── Datos por rol (sin institución seleccionada) ───────────────────────

    private function datosPersonal(int $instId, Carbon $hoy): array
    {
        $items = ItemInforme::whereHas(
            'informe',
            fn ($q) => $q->where('id_institucion', $instId)->where('fecha', $hoy->toDateString())
        )->with('usuario', 'designacion')->get();

        $semanaLabels    = [];
        $semanaPresentes = [];
        $semanaAusentes  = [];
        $semanaTardanzas = [];

        for ($i = 4; $i >= 0; $i--) {
            $dia            = $hoy->copy()->subWeekdays($i);
            $semanaLabels[] = $dia->isoFormat('ddd D/M');
            $diasItems      = ItemInforme::whereHas(
                'informe',
                fn ($q) => $q->where('id_institucion', $instId)->where('fecha', $dia->toDateString())
            );
            $semanaPresentes[] = (clone $diasItems)->where('tipo_novedad', 'presente')->count();
            $semanaAusentes[]  = (clone $diasItems)->whereIn('tipo_novedad', ['ausencia_justificada', 'ausencia_injustificada'])->count();
            $semanaTardanzas[] = (clone $diasItems)->where('tipo_novedad', 'tardanza')->count();
        }

        return [
            'stats' => [
                'presentes'      => $items->where('tipo_novedad', 'presente')->count(),
                'ausentes'       => $items->whereIn('tipo_novedad', ['ausencia_justificada', 'ausencia_injustificada'])->count(),
                'tardanzas'      => $items->where('tipo_novedad', 'tardanza')->count(),
                'sin_justificar' => $items->where('tipo_novedad', 'ausencia_injustificada')->count(),
            ],
            'urgentes'        => $items->where('requiere_atencion', true)->values(),
            'semanaLabels'    => $semanaLabels,
            'semanaPresentes' => $semanaPresentes,
            'semanaAusentes'  => $semanaAusentes,
            'semanaTardanzas' => $semanaTardanzas,
        ];
    }

    private function datosDirector(int $instId, Carbon $hoy): array
    {
        $items = ItemInforme::whereHas(
            'informe',
            fn ($q) => $q->where('id_institucion', $instId)->where('fecha', $hoy->toDateString())
        )->get();

        return [
            'stats' => [
                'personal'  => Designacion::vigente()->porInstitucion($instId)->count(),
                'presentes' => $items->where('tipo_novedad', 'presente')->count(),
                'ausentes'  => $items->whereIn('tipo_novedad', ['ausencia_justificada', 'ausencia_injustificada'])->count(),
            ],
        ];
    }

    private function datosUsuario(User $user, Carbon $hoy, BancoHorasService $bancoService): array
    {
        $designacionVigente = $user->designaciones()->vigente()->first();

        $marcaHoy = $designacionVigente
            ? MarcaComputada::deUsuario($user->id)
                ->enFecha($hoy)
                ->where('id_designacion', $designacionVigente->id)
                ->first()
            : null;

        $ddjjVigente = $designacionVigente
            ? $designacionVigente->declaracionesJuradas()
                ->where('estado', 'aprobada')
                ->activas()
                ->with('horarios')
                ->latest('fecha_inicio')
                ->first()
            : null;

        $saldoBanco = $designacionVigente
            ? $bancoService->consultarSaldo($user, $designacionVigente)
            : 0;

        $ultimosAvisos = $user->avisos()->orderByDesc('fecha')->limit(3)->get()
            ->map(fn ($a) => ['tipo' => 'Aviso: ' . $a->tipo, 'fecha' => $a->fecha->format('d/m/Y')]);
        $ultimasLicencias = $user->declaracionesJuradas()
            ->with('designacion.tipoLicencia')
            ->orderByDesc('fecha_inicio')->limit(3)->get()
            ->map(fn ($l) => ['tipo' => 'DDJJ ' . $l->estado, 'fecha' => $l->fecha_inicio->format('d/m/Y')]);

        return [
            'marcaHoy'           => $marcaHoy,
            'ddjjVigente'        => $ddjjVigente,
            'saldoBanco'         => $saldoBanco,
            'ultimosMovimientos' => $ultimosAvisos->merge($ultimasLicencias)
                ->sortByDesc('fecha')->take(5)->values(),
        ];
    }
}
