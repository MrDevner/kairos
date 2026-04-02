<?php

namespace App\Http\Controllers;

use App\Models\Designacion;
use App\Models\Dispositivo;
use App\Models\Institucion;
use App\Models\ItemInforme;
use App\Models\MarcaComputada;
use App\Models\MarcaOriginal;
use App\Models\Usuario;
use App\Services\BancoHorasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, BancoHorasService $bancoService)
    {
        /** @var Usuario $user */
        $user   = $request->user();
        $instId = (int) session('institucion_activa_id', 0);
        $hoy    = Carbon::today();

        $data = [];

        if ($user->hasRole('Administrador General')) {
            $data = $this->datosAdminGeneral($hoy);

        } elseif ($user->tieneRolEnInstitucion('Jefe de Personal', $instId)
               || $user->tieneRolEnInstitucion('Departamento Personal', $instId)) {
            $data = $this->datosPersonal($instId, $hoy);

        } elseif ($user->tieneRolEnInstitucion('Director Administrativo', $instId)
               || $user->tieneRolEnInstitucion('Auditor', $instId)) {
            $data = $this->datosDirector($instId, $hoy);

        } else {
            $data = $this->datosUsuario($user, $hoy, $bancoService);
        }

        return view('home', $data);
    }

    // ── Datos por rol ──────────────────────────────────────────────────────

    private function datosAdminGeneral(Carbon $hoy): array
    {
        $labels = [];
        $valores = [];
        for ($i = 29; $i >= 0; $i--) {
            $fecha = $hoy->copy()->subDays($i);
            $labels[]  = $fecha->format('d/m');
            $valores[] = MarcaOriginal::whereDate('fecha_hora', $fecha)->count();
        }

        return [
            'stats' => [
                'instituciones' => Institucion::activas()->count(),
                'usuarios'      => Usuario::where('activo', true)->count(),
                'dispositivos'  => Dispositivo::activos()->count(),
                'errores_hoy'   => MarcaComputada::enFecha($hoy)->conErrores()->count(),
            ],
            'chartLabels'    => $labels,
            'chartData'      => $valores,
            'erroresCriticos' => [],
        ];
    }

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
            $dia = $hoy->copy()->subWeekdays($i);
            $semanaLabels[] = $dia->isoFormat('ddd D/M');
            $diasItems = ItemInforme::whereHas(
                'informe',
                fn ($q) => $q->where('id_institucion', $instId)->where('fecha', $dia->toDateString())
            );
            $semanaPresentes[] = (clone $diasItems)->where('tipo_novedad', 'presente')->count();
            $semanaAusentes[]  = (clone $diasItems)->whereIn('tipo_novedad', ['ausencia_justificada','ausencia_injustificada'])->count();
            $semanaTardanzas[] = (clone $diasItems)->where('tipo_novedad', 'tardanza')->count();
        }

        return [
            'stats' => [
                'presentes'     => $items->where('tipo_novedad', 'presente')->count(),
                'ausentes'      => $items->whereIn('tipo_novedad', ['ausencia_justificada','ausencia_injustificada'])->count(),
                'tardanzas'     => $items->where('tipo_novedad', 'tardanza')->count(),
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
                'ausentes'  => $items->whereIn('tipo_novedad', ['ausencia_justificada','ausencia_injustificada'])->count(),
            ],
        ];
    }

    private function datosUsuario(Usuario $user, Carbon $hoy, BancoHorasService $bancoService): array
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

        // Últimos 5 avisos + licencias
        $ultimosAvisos = $user->avisos()->orderByDesc('fecha')->limit(3)->get()
            ->map(fn ($a) => ['tipo' => 'Aviso: ' . $a->tipo, 'fecha' => $a->fecha->format('d/m/Y')]);
        $ultimasLicencias = $user->declaracionesJuradas()
            ->with('designacion.tipoLicencia')
            ->orderByDesc('fecha_inicio')->limit(3)->get()
            ->map(fn ($l) => ['tipo' => 'DDJJ ' . $l->estado, 'fecha' => $l->fecha_inicio->format('d/m/Y')]);

        return [
            'marcaHoy'         => $marcaHoy,
            'ddjjVigente'      => $ddjjVigente,
            'saldoBanco'       => $saldoBanco,
            'ultimosMovimientos' => $ultimosAvisos->merge($ultimasLicencias)
                ->sortByDesc('fecha')->take(5)->values(),
        ];
    }
}
