<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\Institucion;
use App\Models\RolInstitucion;
use App\Models\TipoLicencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvisoController extends Controller
{
    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $auth   = auth()->user();

        $query = Aviso::with([
            'usuario',
            'designacion.dependencia',
            'institucion',
            'tipoLicencia',
            'registradoPor',
        ])->orderByDesc('fecha_evento')->orderByDesc('fecha_aviso');

        // Filtro por institución activa
        if ($instId) {
            $query->deInstitucion($instId);
        }

        // Visibilidad según rol
        if (! $auth->permisos()->administrador()->tieneTodosLosPermisos()) {
            $query->where(function ($q) use ($auth) {
                // El propio usuario ve sus propios avisos
                $q->where('id_usuario', $auth->id);

                // Departamento Personal (o rol superior): ve los de las instituciones donde tiene ese rol
                $instsDep = \DB::table('roles_institucion_usuario as riu')
                    ->join('roles_institucion as ri', 'ri.id', '=', 'riu.id_rol_institucion')
                    ->where('riu.id_usuario', $auth->id)
                    ->where('ri.nivel', '<=', RolInstitucion::NIVEL_GESTION)
                    ->where(function ($sq) {
                        $sq->whereNull('riu.fecha_hasta')
                           ->orWhere('riu.fecha_hasta', '>=', now());
                    })
                    ->pluck('riu.id_institucion');

                if ($instsDep->isNotEmpty()) {
                    $q->orWhereIn('id_institucion', $instsDep);
                }

                // Jefe de dependencia: ve los de su dependencia
                $depIdsJefe = \DB::table('jefaturas')
                    ->where('id_usuario', $auth->id)
                    ->where('activa', true)
                    ->where('fecha_desde', '<=', now())
                    ->where(function ($sq) {
                        $sq->whereNull('fecha_hasta')->orWhere('fecha_hasta', '>=', now());
                    })
                    ->pluck('id_dependencia');

                if ($depIdsJefe->isNotEmpty()) {
                    $q->orWhereHas('designacion', fn ($sub) =>
                        $sub->whereIn('id_dependencia', $depIdsJefe)
                    );
                }
            });
        }

        $fechaDesde = $request->input('fecha_desde', now()->subMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', '');

        // Filtros de búsqueda (sobre fecha_evento, que es la fecha de la ausencia/tardanza)
        if ($fechaDesde) {
            $query->where('fecha_evento', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->where('fecha_evento', '<=', $fechaHasta);
        }
        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }
        if ($request->filled('dependencia')) {
            $query->deDependencia((int) $request->dependencia);
        }

        $avisos = $query->paginate(25)->withQueryString();

        $dependencias = $instId
            ? Dependencia::deInstitucion($instId)->dependenciasActivas()->orderBy('nombre')->get()
            : collect();

        $puedeCrear = $this->puedeCrearAvisos($auth, $instId);

        return view('avisos.index', compact('avisos', 'dependencias', 'puedeCrear', 'fechaDesde', 'fechaHasta'));
    }

    public function create(): View
    {
        $auth   = auth()->user();
        $instId = (int) session('institucion_activa_id', 0);

        abort_unless($this->puedeCrearAvisos($auth, $instId), 403);

        $instituciones  = $this->institucionesPermitidas($auth);
        $tiposLicencia  = $instId
            ? TipoLicencia::permitidosParaAvisoEnInstitucion($instId)->activos()->orderBy('nombre')->get()
            : TipoLicencia::activos()->orderBy('nombre')->get();

        return view('avisos.create', compact('instituciones', 'tiposLicencia', 'instId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth   = auth()->user();
        $instId = (int) $request->input('id_institucion', session('institucion_activa_id', 0));

        abort_unless($this->puedeCrearAvisos($auth, $instId), 403);

        $data = $request->validate([
            'id_usuario'            => ['required', 'integer', 'exists:usuarios,id'],
            'id_designacion'        => ['required', 'integer', 'exists:designaciones,id'],
            'id_institucion'        => ['required', 'integer', 'exists:instituciones,id'],
            'tipo'                  => ['required', 'in:ausencia,tardanza'],
            'fecha_aviso'           => ['required', 'date'],
            'fecha_evento'          => ['required', 'date'],
            'hora_estimada_llegada' => ['nullable', 'date_format:H:i',
                                        'required_if:tipo,tardanza'],
            'id_tipo_licencia'      => ['nullable', 'integer', 'exists:tipos_licencia,id',
                                        'required_if:tipo,ausencia'],
            'motivo'                => ['nullable', 'string', 'max:1000'],
        ]);

        // Si es tardanza, limpiar el tipo de licencia y viceversa
        if ($data['tipo'] === 'tardanza') {
            $data['id_tipo_licencia'] = null;
        } else {
            $data['hora_estimada_llegada'] = null;
        }

        Aviso::create(array_merge($data, ['id_registrado_por' => $auth->id]));

        return redirect()->route('avisos.index')->with('success', 'Aviso registrado correctamente.');
    }

    public function show(Aviso $aviso): View
    {
        $this->authorize('ver', $aviso);
        $aviso->load(['usuario', 'designacion.dependencia', 'institucion', 'tipoLicencia', 'registradoPor']);

        return view('avisos.show', compact('aviso'));
    }

    public function edit(Aviso $aviso): View
    {
        $this->authorize('editar', $aviso);

        $tiposLicencia = TipoLicencia::permitidosParaAvisoEnInstitucion($aviso->id_institucion)
            ->activos()->orderBy('nombre')->get();

        return view('avisos.edit', compact('aviso', 'tiposLicencia'));
    }

    public function update(Request $request, Aviso $aviso): RedirectResponse
    {
        $this->authorize('editar', $aviso);

        $data = $request->validate([
            'tipo'                  => ['required', 'in:ausencia,tardanza'],
            'fecha_aviso'           => ['required', 'date'],
            'fecha_evento'          => ['required', 'date'],
            'hora_estimada_llegada' => ['nullable', 'date_format:H:i',
                                        'required_if:tipo,tardanza'],
            'id_tipo_licencia'      => ['nullable', 'integer', 'exists:tipos_licencia,id',
                                        'required_if:tipo,ausencia'],
            'motivo'                => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['tipo'] === 'tardanza') {
            $data['id_tipo_licencia'] = null;
        } else {
            $data['hora_estimada_llegada'] = null;
        }

        $aviso->update($data);

        return redirect()->route('avisos.show', $aviso)->with('success', 'Aviso actualizado.');
    }

    public function destroy(Aviso $aviso): RedirectResponse
    {
        $this->authorize('eliminar', $aviso);
        $aviso->delete();

        return redirect()->route('avisos.index')->with('success', 'Aviso eliminado.');
    }

    /**
     * AJAX: devuelve las designaciones vigentes de un usuario para popular el select.
     */
    public function designacionesPorUsuario(Request $request): JsonResponse
    {
        $idUsuario = (int) $request->get('id_usuario');
        if (! $idUsuario) {
            return response()->json([]);
        }

        $result = Designacion::with(['cargo', 'dependencia', 'institucion'])
            ->where('id_usuario', $idUsuario)
            ->vigente()
            ->get()
            ->map(fn ($d) => [
                'id'             => $d->id,
                'text'           => ($d->cargo->nombre ?? '—') . ' — ' . ($d->dependencia->nombre ?? '—'),
                'id_institucion' => $d->id_institucion,
                'institucion'    => $d->institucion->nombre ?? '—',
            ]);

        return response()->json($result);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function puedeCrearAvisos($auth, int $instId): bool
    {
        if ($auth->permisos()->administrador()->tieneTodosLosPermisos()) {
            return true;
        }

        if ($instId && RolInstitucion::nivelMinimoDeUsuario($auth->id, $instId) <= RolInstitucion::NIVEL_GESTION) {
            return true;
        }

        // Sin institución activa: verifica si tiene algún rol gestor en cualquier institución
        return \DB::table('roles_institucion_usuario as riu')
            ->join('roles_institucion as ri', 'ri.id', '=', 'riu.id_rol_institucion')
            ->where('riu.id_usuario', $auth->id)
            ->where('ri.nivel', '<=', RolInstitucion::NIVEL_GESTION)
            ->where(function ($q) {
                $q->whereNull('riu.fecha_hasta')
                  ->orWhere('riu.fecha_hasta', '>=', now());
            })
            ->exists();
    }

    private function institucionesPermitidas($auth)
    {
        if ($auth->permisos()->administrador()->tieneTodosLosPermisos()) {
            return Institucion::activas()->orderBy('nombre')->get();
        }

        $ids = \DB::table('roles_institucion_usuario as riu')
            ->join('roles_institucion as ri', 'ri.id', '=', 'riu.id_rol_institucion')
            ->where('riu.id_usuario', $auth->id)
            ->where('ri.nivel', '<=', RolInstitucion::NIVEL_GESTION)
            ->where(function ($q) {
                $q->whereNull('riu.fecha_hasta')
                  ->orWhere('riu.fecha_hasta', '>=', now());
            })
            ->pluck('riu.id_institucion');

        return Institucion::whereIn('id', $ids)->activas()->orderBy('nombre')->get();
    }
}
