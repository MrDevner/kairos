<?php

namespace App\Http\Controllers;

use App\Models\Designacion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    // Etiquetas legibles para los tipos de modelos
    private const MODELOS = [
        'App\Models\Aviso'               => 'Aviso',
        'App\Models\BancoHoras'          => 'Banco de Horas',
        'App\Models\Cargo'               => 'Cargo',
        'App\Models\CategoriaCargo'      => 'Categoría de Cargo',
        'App\Models\ComputadorAutorizado'=> 'Computador Autorizado',
        'App\Models\CondicionEvento'     => 'Condición de Evento',
        'App\Models\DeclaracionJurada'   => 'DDJJ',
        'App\Models\Dependencia'         => 'Dependencia',
        'App\Models\Designacion'         => 'Designación',
        'App\Models\Dispositivo'         => 'Dispositivo',
        'App\Models\EventoCalendario'    => 'Evento de Calendario',
        'App\Models\HorarioDdjj'         => 'Horario DDJJ',
        'App\Models\InformeDiario'       => 'Informe Diario',
        'App\Models\Institucion'         => 'Institución',
        'App\Models\ItemInforme'         => 'Ítem de Informe',
        'App\Models\Jefatura'            => 'Jefatura',
        'App\Models\Licencia'            => 'Licencia',
        'App\Models\MarcaComputada'      => 'Marca Computada',
        'App\Models\MarcaOriginal'       => 'Marca Original',
        'App\Models\MovimientoBancoHoras'=> 'Movimiento Banco Horas',
        'App\Models\PermisoRol'          => 'Permiso de Rol',
        'App\Models\RolInstitucion'      => 'Rol de Institución',
        'App\Models\RolInstitucionUsuario'=> 'Asignación de Rol',
        'App\Models\TiempoExtra'         => 'Tiempo Extra',
        'App\Models\TipoLicencia'        => 'Tipo de Licencia',
        'App\Models\Usuario'             => 'Usuario',
    ];

    public function index(Request $request): View
    {
        $auth           = auth()->user();
        $esAdminGeneral = $auth->hasRole('Administrador General');
        $instId         = (int) session('institucion_activa_id', 0);
        $esAdminInst    = $instId && $this->esAdminInstitucion($auth, $instId);

        $query = Activity::query()->orderByDesc('created_at');

        // Visibilidad según rol
        if ($esAdminGeneral) {
            // ve todo
        } elseif ($esAdminInst) {
            $usuarioIds = Designacion::where('id_institucion', $instId)
                ->distinct()->pluck('id_usuario');
            $query->where('causer_type', Usuario::class)
                  ->whereIn('causer_id', $usuarioIds);
        } else {
            $query->where('causer_type', Usuario::class)
                  ->where('causer_id', $auth->id);
        }

        // Filtros opcionales
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }
        if ($request->filled('evento')) {
            $query->where('event', $request->evento);
        }
        if ($request->filled('modelo')) {
            $query->where('subject_type', $request->modelo);
        }
        if (($esAdminGeneral || $esAdminInst) && $request->filled('causer_id')) {
            $query->where('causer_type', Usuario::class)
                  ->where('causer_id', (int) $request->causer_id);
        }

        $logs = $query->with('causer')->paginate(50)->withQueryString();

        // Lista de modelos presentes para el filtro
        $modelosPresentes = Activity::query()
            ->select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->orderBy('subject_type')
            ->pluck('subject_type');

        // Usuarios disponibles para el filtro (solo admin)
        $usuariosFiltro = collect();
        if ($esAdminGeneral) {
            $usuariosFiltro = Usuario::orderBy('apellidos')->orderBy('nombres')->get();
        } elseif ($esAdminInst) {
            $ids = Designacion::where('id_institucion', $instId)->distinct()->pluck('id_usuario');
            $usuariosFiltro = Usuario::whereIn('id', $ids)->orderBy('apellidos')->orderBy('nombres')->get();
        }

        $modelos = self::MODELOS;

        return view('logs.index', compact(
            'logs', 'esAdminGeneral', 'esAdminInst',
            'modelosPresentes', 'usuariosFiltro', 'modelos'
        ));
    }

    public function show(Activity $activity): View
    {
        $auth           = auth()->user();
        $esAdminGeneral = $auth->hasRole('Administrador General');
        $instId         = (int) session('institucion_activa_id', 0);
        $esAdminInst    = $instId && $this->esAdminInstitucion($auth, $instId);

        // Autorización
        if (! $esAdminGeneral) {
            if ($esAdminInst) {
                $usuarioIds = Designacion::where('id_institucion', $instId)
                    ->distinct()->pluck('id_usuario');
                abort_unless(
                    $activity->causer_type === Usuario::class && $usuarioIds->contains($activity->causer_id),
                    403
                );
            } else {
                abort_unless(
                    $activity->causer_type === Usuario::class && $activity->causer_id === $auth->id,
                    403
                );
            }
        }

        $activity->load('causer');
        $modelos = self::MODELOS;

        return view('logs.show', compact('activity', 'modelos'));
    }

    public static function labelModelo(?string $class): string
    {
        return self::MODELOS[$class] ?? class_basename((string) $class);
    }

    private function esAdminInstitucion(Usuario $user, int $instId): bool
    {
        return DB::table('roles_institucion_usuario as riu')
            ->join('roles_institucion as ri', 'ri.id', '=', 'riu.id_rol_institucion')
            ->where('riu.id_usuario', $user->id)
            ->where('riu.id_institucion', $instId)
            ->where('riu.activo', true)
            ->where('riu.fecha_desde', '<=', now())
            ->where(fn ($q) => $q->whereNull('riu.fecha_hasta')->orWhere('riu.fecha_hasta', '>=', now()))
            ->whereIn('ri.nombre', ['Administrador', 'Director Administrativo', 'Jefe de Personal'])
            ->exists();
    }
}
