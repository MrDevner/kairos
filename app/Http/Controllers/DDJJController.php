<?php

namespace App\Http\Controllers;

use App\Http\Requests\DDJJStoreRequest;
use App\Http\Requests\DDJJUpdateRequest;
use App\Models\DeclaracionJurada;
use App\Models\Designacion;
use App\Models\Edificio;
use App\Models\HorarioDdjj;
use App\Models\RolInstitucion;
use App\Models\User;
use App\Services\DDJJService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DDJJController extends Controller
{
    public function __construct(private readonly DDJJService $service) {}

    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = DeclaracionJurada::with(['usuario', 'designacion.cargo', 'designacion.institucion'])
            ->orderByDesc('fecha_inicio');

        // Usuario común solo ve las suyas
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $user->permisos()->administrador()->tieneTodosLosPermisos()
            ? 0
            : RolInstitucion::nivelMinimoDeUsuario($user->id, $instId);

        if ($nivelActor > RolInstitucion::NIVEL_GESTION) {
            $query->deUsuario($user->id);
        }

        if ($request->filled('estado')) {
            $query->enEstado($request->estado);
        }

        $ddjjs = $query->paginate(25)->withQueryString();
        return view('ddjj.index', compact('ddjjs'));
    }

    public function create(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user           = $request->user();
        $puedeGestionar = $this->puedeGestionar($request);
        $edificios      = $this->edificiosDeInstitucionActiva();

        return view('ddjj.create', compact('puedeGestionar', 'user', 'edificios'));
    }

    /**
     * AJAX: búsqueda select2 de trabajadores con designación vigente en la institución activa.
     * Solo disponible para usuarios con nivel de gestión.
     */
    public function buscarTrabajadores(Request $request): JsonResponse
    {
        if (!$this->puedeGestionar($request)) {
            return response()->json(['results' => []], 403);
        }

        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json(['results' => []]);
        }

        $instId = (int) session('institucion_activa_id', 0);

        $results = User::where('activo', true)
            ->whereHas('designaciones', fn ($q2) => $q2->vigente()->porInstitucion($instId))
            ->where(fn ($query) => $query->where('apellidos', 'like', "%{$q}%")
                ->orWhere('nombres', 'like', "%{$q}%")
                ->orWhere('documento', 'like', "%{$q}%"))
            ->orderBy('apellidos')->orderBy('nombres')
            ->limit(25)
            ->get(['id', 'apellidos', 'nombres', 'documento'])
            ->map(fn ($u) => [
                'id'   => $u->id,
                'text' => $u->apellidos . ', ' . $u->nombres . ($u->documento ? ' (' . $u->documento . ')' : ''),
            ]);

        return response()->json(['results' => $results]);
    }

    /**
     * AJAX: designaciones vigentes de un usuario en la institución activa.
     */
    public function designacionesActivasDeUsuario(Request $request): JsonResponse
    {
        $actor          = $request->user();
        $puedeGestionar = $this->puedeGestionar($request);
        $idUsuario      = (int) $request->get('id_usuario');

        if (!$idUsuario) {
            return response()->json([]);
        }

        if (!$puedeGestionar && $idUsuario !== $actor->id) {
            return response()->json([], 403);
        }

        $instId = (int) session('institucion_activa_id', 0);

        $result = Designacion::with(['cargo', 'dependencia', 'institucion'])
            ->where('id_usuario', $idUsuario)
            ->porInstitucion($instId)
            ->vigente()
            ->get()
            ->map(fn ($d) => [
                'id'                 => $d->id,
                'text'               => ($d->cargo->nombre ?? '—') . ' — ' . ($d->dependencia->nombre ?? '—'),
                'horas_obligatorias' => $d->horasSemanalesObligatorias(),
            ]);

        return response()->json($result);
    }

    public function store(DDJJStoreRequest $request): RedirectResponse
    {
        $actor          = $request->user();
        $puedeGestionar = $this->puedeGestionar($request);
        $instId         = (int) session('institucion_activa_id', 0);

        // Nunca confiar en id_usuario del payload si el actor no tiene nivel de gestión.
        $idUsuarioObjetivo = $puedeGestionar ? (int) $request->input('id_usuario') : $actor->id;
        $usuarioObjetivo   = User::findOrFail($idUsuarioObjetivo);

        $horariosPorDesignacion = collect($request->input('horarios'))->groupBy('id_designacion');

        // Integridad: cada id_designacion debe pertenecer al usuario objetivo,
        // estar vigente y ser de la institución activa.
        $designaciones = Designacion::whereIn('id', $horariosPorDesignacion->keys())
            ->where('id_usuario', $idUsuarioObjetivo)
            ->porInstitucion($instId)
            ->vigente()
            ->get()
            ->keyBy('id');

        if ($designaciones->count() !== $horariosPorDesignacion->count()) {
            return back()->withErrors([
                'horarios' => 'Una o más designaciones seleccionadas no son válidas, no están vigentes o no pertenecen al trabajador indicado.',
            ])->withInput();
        }

        // Superposición evaluada contra TODAS las designaciones del usuario a la vez.
        try {
            $this->service->validarSuperposicion($usuarioObjetivo, $request->input('horarios'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        // Horas máximas: no bloqueante, evaluado por designación.
        $warnings = [];
        foreach ($horariosPorDesignacion as $idDesignacion => $horarios) {
            $warning = $this->service->advertenciaHorasMaximas($designaciones[(int) $idDesignacion], $horarios->all());
            if ($warning) {
                $warnings[] = $warning;
            }
        }

        $ddjjsCreadas = [];
        DB::transaction(function () use (&$ddjjsCreadas, $horariosPorDesignacion, $idUsuarioObjetivo, $request) {
            foreach ($horariosPorDesignacion as $idDesignacion => $horarios) {
                $ddjj = DeclaracionJurada::create([
                    'id_usuario'     => $idUsuarioObjetivo,
                    'id_designacion' => (int) $idDesignacion,
                    'fecha_inicio'   => $request->fecha_inicio,
                    'fecha_fin'      => $request->fecha_fin,
                    'estado'         => 'borrador',
                    'observaciones'  => $request->observaciones,
                ]);

                foreach ($horarios as $h) {
                    HorarioDdjj::create([
                        'id_declaracion_jurada'  => $ddjj->id,
                        'dia_semana'             => $h['dia_semana'],
                        'hora_entrada'           => $h['hora_entrada'],
                        'hora_salida'            => $h['hora_salida'],
                        'modalidad'              => $h['modalidad'],
                        'id_institucion_externa' => $h['id_institucion_externa'] ?? null,
                        'id_dependencia'         => $h['id_dependencia'] ?? null,
                        'id_edificio'            => $h['id_edificio'] ?? null,
                        'id_oficina'             => $h['id_oficina'] ?? null,
                    ]);
                }

                $ddjjsCreadas[] = $ddjj;
            }
        });

        $mensaje = count($ddjjsCreadas) === 1
            ? 'Declaración jurada guardada como borrador.'
            : count($ddjjsCreadas) . ' declaraciones juradas guardadas como borrador.';

        $redirect = count($ddjjsCreadas) === 1
            ? redirect()->route('ddjj.show', $ddjjsCreadas[0])
            : redirect()->route('ddjj.index');

        $redirect->with('success', $mensaje);
        if ($warnings) {
            $redirect->with('warning', implode(' ', $warnings));
        }

        return $redirect;
    }

    public function show(DeclaracionJurada $ddjj): View
    {
        $ddjj->load([
            'usuario', 'designacion.cargo', 'designacion.institucion',
            'horarios.institucionExterna', 'horarios.dependencia',
            'horarios.edificio', 'horarios.oficina',
        ]);
        return view('ddjj.show', compact('ddjj'));
    }

    public function edit(DeclaracionJurada $ddjj): View
    {
        abort_unless($ddjj->esBorrador(), 403, 'Solo se puede editar una DDJJ en borrador.');
        $ddjj->load('horarios', 'designacion.cargo');
        $edificios = $this->edificiosDeInstitucionActiva($ddjj->designacion->id_institucion);
        return view('ddjj.edit', compact('ddjj', 'edificios'));
    }

    public function update(DDJJUpdateRequest $request, DeclaracionJurada $ddjj): RedirectResponse
    {
        abort_unless($ddjj->esBorrador(), 403);

        try {
            $this->service->validarSuperposicion($ddjj->usuario, $request->horarios, $ddjj);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $warning = $this->service->advertenciaHorasMaximas($ddjj->designacion, $request->horarios);

        $ddjj->update([
            'fecha_inicio'  => $request->fecha_inicio,
            'fecha_fin'     => $request->fecha_fin,
            'observaciones' => $request->observaciones,
        ]);

        $ddjj->horarios()->delete();
        foreach ($request->horarios as $h) {
            HorarioDdjj::create(array_merge($h, ['id_declaracion_jurada' => $ddjj->id]));
        }

        $redirect = redirect()->route('ddjj.show', $ddjj)->with('success', 'DDJJ actualizada.');
        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function destroy(DeclaracionJurada $ddjj): RedirectResponse
    {
        abort_unless($ddjj->esBorrador(), 403, 'Solo se puede eliminar un borrador.');
        $ddjj->horarios()->delete();
        $ddjj->delete();
        return redirect()->route('ddjj.index')->with('success', 'DDJJ eliminada.');
    }

    public function presentar(DeclaracionJurada $ddjj): RedirectResponse
    {
        try {
            $this->service->presentar($ddjj);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'DDJJ presentada correctamente.');
    }

    public function aprobar(Request $request, DeclaracionJurada $ddjj): RedirectResponse
    {
        try {
            $this->service->aprobar($ddjj, $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'DDJJ aprobada.');
    }

    public function rechazar(Request $request, DeclaracionJurada $ddjj): RedirectResponse
    {
        $request->validate(['observaciones_rechazo' => ['required', 'string', 'max:1000']]);
        $ddjj->update([
            'estado'        => 'rechazada',
            'observaciones' => trim(($ddjj->observaciones ?? '') . "\nRechazada: " . $request->observaciones_rechazo),
        ]);
        return back()->with('success', 'DDJJ rechazada.');
    }

    private function puedeGestionar(Request $request): bool
    {
        $actor          = $request->user();
        $instId         = (int) session('institucion_activa_id', 0);
        $esAdminGeneral = $actor->permisos()->administrador()->tieneTodosLosPermisos();
        $nivelActor     = $esAdminGeneral ? 0 : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);
        return $esAdminGeneral || $nivelActor <= RolInstitucion::NIVEL_GESTION;
    }

    /**
     * Edificios activos (con sus oficinas activas) de una institución, en un
     * shape plano listo para embeber como JSON en las vistas de DDJJ.
     */
    private function edificiosDeInstitucionActiva(?int $instId = null): array
    {
        $instId ??= (int) session('institucion_activa_id', 0);

        return Edificio::activos()->deInstitucion($instId)
            ->with(['oficinas' => fn ($q) => $q->activas()->orderBy('nombre')])
            ->orderBy('nombre')->get()
            ->map(fn (Edificio $e) => [
                'id'       => $e->id,
                'nombre'   => $e->nombre,
                'oficinas' => $e->oficinas->map(fn ($o) => ['id' => $o->id, 'nombre' => $o->nombre])->all(),
            ])
            ->all();
    }
}
