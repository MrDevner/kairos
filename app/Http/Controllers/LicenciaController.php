<?php

namespace App\Http\Controllers;

use App\Models\Designacion;
use App\Models\Licencia;
use App\Models\TipoLicencia;
use App\Models\Usuario;
use App\Services\LicenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenciaController extends Controller
{
    public function __construct(private readonly LicenciaService $service) {}

    public function index(Request $request): View
    {
        $query = Licencia::with(['usuario', 'tipoLicencia', 'registradoPor'])
            ->orderByDesc('fecha_inicio');

        if ($request->filled('estado'))   $query->enEstado($request->estado);
        if ($request->filled('usuario'))  $query->deUsuario((int) $request->usuario);
        if ($request->filled('tipo'))     $query->where('id_tipo_licencia', (int) $request->tipo);
        if ($request->filled('desde') && $request->filled('hasta'))
            $query->whereBetween('fecha_inicio', [$request->desde, $request->hasta]);

        $licencias = $query->paginate(25)->withQueryString();
        $tipos     = TipoLicencia::activos()->orderBy('nombre')->get();

        return view('licencias.index', compact('licencias', 'tipos'));
    }

    public function create(): View
    {
        $usuarios     = Usuario::where('activo', true)->orderBy('apellidos')->get();
        $tipos        = TipoLicencia::activos()->orderBy('nombre')->get();
        $designaciones = Designacion::vigente()->with('cargo', 'usuario')->get();
        return view('licencias.create', compact('usuarios', 'tipos', 'designaciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario'        => ['required', 'integer', 'exists:usuarios,id'],
            'id_tipo_licencia'  => ['required', 'integer', 'exists:tipos_licencia,id'],
            'id_designacion'    => ['nullable', 'integer', 'exists:designaciones,id'],
            'fecha_inicio'      => ['required', 'date'],
            'fecha_fin'         => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo'            => ['nullable', 'string', 'max:1000'],
            'documentacion'     => ['nullable', 'file', 'max:5120'],
        ]);

        if ($request->hasFile('documentacion')) {
            $data['documentacion'] = $request->file('documentacion')->store('licencias', 'public');
        }

        try {
            $licencia = $this->service->registrar($data, $request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('licencias.show', $licencia)->with('success', 'Licencia registrada.');
    }

    public function show(Licencia $licencia): View
    {
        $licencia->load(['usuario', 'tipoLicencia', 'designacion', 'registradoPor', 'aprobadoPor']);
        return view('licencias.show', compact('licencia'));
    }

    public function edit(Licencia $licencia): View
    {
        abort_unless($licencia->estaPendiente(), 403, 'Solo se pueden editar licencias pendientes.');
        $usuarios  = Usuario::where('activo', true)->orderBy('apellidos')->get();
        $tipos     = TipoLicencia::activos()->orderBy('nombre')->get();
        return view('licencias.edit', compact('licencia', 'usuarios', 'tipos'));
    }

    public function update(Request $request, Licencia $licencia): RedirectResponse
    {
        abort_unless($licencia->estaPendiente(), 403);
        $data = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo'       => ['nullable', 'string'],
        ]);
        $licencia->update($data);
        return redirect()->route('licencias.show', $licencia)->with('success', 'Licencia actualizada.');
    }

    public function destroy(Licencia $licencia): RedirectResponse
    {
        abort_unless($licencia->estaPendiente(), 403, 'Solo se pueden eliminar licencias pendientes.');
        $licencia->delete();
        return redirect()->route('licencias.index')->with('success', 'Licencia eliminada.');
    }

    public function aprobar(Request $request, Licencia $licencia): RedirectResponse
    {
        try {
            $this->service->aprobar($licencia, $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Licencia aprobada. Días computados: ' . $licencia->fresh()->dias_computados);
    }

    public function rechazar(Request $request, Licencia $licencia): RedirectResponse
    {
        $obs = $request->validate(['observaciones' => ['required', 'string']])['observaciones'];
        try {
            $this->service->rechazar($licencia, $request->user(), $obs);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Licencia rechazada.');
    }
}
