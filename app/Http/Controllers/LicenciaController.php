<?php

namespace App\Http\Controllers;

use App\Models\Designacion;
use App\Models\Licencia;
use App\Models\TipoLicencia;
use App\Models\Usuario;
use App\Services\LicenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LicenciaController extends Controller
{
    public function __construct(private readonly LicenciaService $service) {}

    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $query  = Licencia::with(['usuario', 'tipoLicencia'])
            ->orderByDesc('fecha_inicio');

        if ($instId) {
            $query->whereHas('designacion', fn ($q) =>
                $q->where('id_institucion', $instId)
            );
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->whereHas('usuario', fn ($q) =>
                $q->where('apellidos', 'like', "%{$b}%")
                  ->orWhere('nombres', 'like', "%{$b}%")
            );
        }

        if ($request->filled('estado'))  $query->enEstado($request->estado);
        if ($request->filled('tipo'))    $query->where('id_tipo_licencia', (int) $request->tipo);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('fecha_inicio', [$request->desde, $request->hasta]);
        } elseif ($request->filled('desde')) {
            $query->where('fecha_inicio', '>=', $request->desde);
        } elseif ($request->filled('hasta')) {
            $query->where('fecha_inicio', '<=', $request->hasta);
        }

        $licencias = $query->paginate(25)->withQueryString();
        $tipos     = TipoLicencia::activos()->orderBy('nombre')->get();

        return view('licencias.index', compact('licencias', 'tipos'));
    }

    public function create(): View
    {
        $instId  = (int) session('institucion_activa_id', 0);
        $usuarios = Usuario::where('activo', true)->orderBy('apellidos')->get();
        $tipos    = TipoLicencia::activos()
            ->when($instId, fn ($q) => $q->visiblesParaInstitucion($instId))
            ->orderBy('nombre')
            ->get();
        $designaciones = Designacion::vigente()
            ->with('cargo', 'institucion', 'usuario')
            ->get();

        return view('licencias.create', compact('usuarios', 'tipos', 'designaciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario'       => ['required', 'integer', 'exists:usuarios,id'],
            'id_tipo_licencia' => ['required', 'integer', 'exists:tipos_licencia,id'],
            'id_designacion'   => ['nullable', 'integer', 'exists:designaciones,id'],
            'fecha_inicio'     => ['required', 'date'],
            'fecha_fin'        => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo'           => ['nullable', 'string', 'max:1000'],
            'documentacion'    => ['nullable', 'file', 'max:5120',
                                   'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        if ($request->hasFile('documentacion')) {
            $data['documentacion'] = $request->file('documentacion')
                ->store('licencias', 'public');
        }

        try {
            $licencia = $this->service->registrar($data, $request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('licencias.show', $licencia)
            ->with('success', 'Licencia registrada.');
    }

    public function show(Licencia $licencia): View
    {
        $licencia->load([
            'usuario',
            'tipoLicencia',
            'designacion.cargo',
            'designacion.institucion',
            'registradoPor',
            'aprobadoPor',
        ]);

        $inst          = $this->institucionDeLicencia($licencia);
        $puedeAutorizar = $inst
            ? $inst->puedeAutorizarLicencias(auth()->user())
            : auth()->user()->hasRole('Administrador General');

        return view('licencias.show', compact('licencia', 'puedeAutorizar'));
    }

    public function edit(Licencia $licencia): View
    {
        abort_unless($licencia->estaPendiente(), 403, 'Solo se pueden editar licencias pendientes.');
        $licencia->load(['usuario', 'tipoLicencia', 'designacion.cargo']);
        return view('licencias.edit', compact('licencia'));
    }

    public function update(Request $request, Licencia $licencia): RedirectResponse
    {
        abort_unless($licencia->estaPendiente(), 403);

        $data = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo'       => ['nullable', 'string', 'max:1000'],
        ]);

        $licencia->update($data);
        return redirect()->route('licencias.show', $licencia)
            ->with('success', 'Licencia actualizada.');
    }

    public function destroy(Licencia $licencia): RedirectResponse
    {
        abort_unless($licencia->estaPendiente(), 403, 'Solo se pueden eliminar licencias pendientes.');

        if ($licencia->documentacion) {
            Storage::disk('public')->delete($licencia->documentacion);
        }

        $licencia->delete();
        return redirect()->route('licencias.index')
            ->with('success', 'Licencia eliminada.');
    }

    public function aprobar(Request $request, Licencia $licencia): RedirectResponse
    {
        $this->autorizarResolucion($licencia, $request);

        try {
            $this->service->aprobar($licencia, $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Licencia aprobada. Días computados: '
            . $licencia->fresh()->dias_computados);
    }

    public function rechazar(Request $request, Licencia $licencia): RedirectResponse
    {
        $this->autorizarResolucion($licencia, $request);

        $obs = $request->validate([
            'observaciones' => ['required', 'string'],
        ])['observaciones'];

        try {
            $this->service->rechazar($licencia, $request->user(), $obs);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Licencia rechazada.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Lanza 403 si el usuario autenticado no puede autorizar esta licencia.
     */
    private function autorizarResolucion(Licencia $licencia, Request $request): void
    {
        $inst = $this->institucionDeLicencia($licencia);

        $puede = $inst
            ? $inst->puedeAutorizarLicencias($request->user())
            : $request->user()->hasRole('Administrador General');

        abort_unless($puede, 403, 'No tiene permiso para autorizar licencias en esta institución.');
    }

    /**
     * Determina la institución a la que pertenece la licencia:
     * primero intenta por la designación asignada, luego por la
     * primera designación vigente del usuario.
     */
    private function institucionDeLicencia(Licencia $licencia): ?\App\Models\Institucion
    {
        if ($licencia->id_designacion) {
            $licencia->loadMissing('designacion.institucion');
            return $licencia->designacion?->institucion;
        }

        return $licencia->usuario
            ->designaciones()
            ->vigente()
            ->with('institucion')
            ->first()
            ?->institucion;
    }
}
