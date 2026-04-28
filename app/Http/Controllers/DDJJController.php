<?php

namespace App\Http\Controllers;

use App\Http\Requests\DDJJRequest;
use App\Models\DeclaracionJurada;
use App\Models\Designacion;
use App\Models\HorarioDdjj;
use App\Models\RolInstitucion;
use App\Services\DDJJService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DDJJController extends Controller
{
    public function __construct(private readonly DDJJService $service) {}

    public function index(Request $request): View
    {
        /** @var \App\Models\Usuario $user */
        $user = $request->user();

        $query = DeclaracionJurada::with(['usuario', 'designacion.cargo', 'designacion.institucion'])
            ->orderByDesc('fecha_inicio');

        // Usuario común solo ve las suyas
        $instId     = (int) session('institucion_activa_id', 0);
        $nivelActor = $user->hasRole('Administrador General')
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
        /** @var \App\Models\Usuario $user */
        $user = $request->user();
        $designaciones = $user->designaciones()->vigente()->with('cargo', 'institucion')->get();
        return view('ddjj.create', compact('designaciones'));
    }

    public function store(DDJJRequest $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $user */
        $user       = $request->user();
        $designacion = Designacion::findOrFail($request->id_designacion);

        try {
            $this->service->validarSuperposicion($user, $request->horarios);
            $this->service->validarHorasMaximas($designacion, $request->horarios);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $ddjj = DeclaracionJurada::create([
            'id_usuario'     => $user->id,
            'id_designacion' => $request->id_designacion,
            'fecha_inicio'   => $request->fecha_inicio,
            'fecha_fin'      => $request->fecha_fin,
            'estado'         => 'borrador',
            'observaciones'  => $request->observaciones,
        ]);

        foreach ($request->horarios as $h) {
            HorarioDdjj::create(array_merge($h, ['id_declaracion_jurada' => $ddjj->id]));
        }

        return redirect()->route('ddjj.show', $ddjj)->with('success', 'DDJJ guardada como borrador.');
    }

    public function show(DeclaracionJurada $ddjj): View
    {
        $ddjj->load(['usuario', 'designacion.cargo', 'designacion.institucion', 'horarios']);
        return view('ddjj.show', compact('ddjj'));
    }

    public function edit(DeclaracionJurada $ddjj): View
    {
        abort_unless($ddjj->esBorrador(), 403, 'Solo se puede editar una DDJJ en borrador.');
        $ddjj->load('horarios', 'designacion.cargo');
        return view('ddjj.edit', compact('ddjj'));
    }

    public function update(DDJJRequest $request, DeclaracionJurada $ddjj): RedirectResponse
    {
        abort_unless($ddjj->esBorrador(), 403);

        try {
            $this->service->validarSuperposicion($ddjj->usuario, $request->horarios, $ddjj);
            $this->service->validarHorasMaximas($ddjj->designacion, $request->horarios);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $ddjj->update([
            'fecha_inicio'  => $request->fecha_inicio,
            'fecha_fin'     => $request->fecha_fin,
            'observaciones' => $request->observaciones,
        ]);

        $ddjj->horarios()->delete();
        foreach ($request->horarios as $h) {
            HorarioDdjj::create(array_merge($h, ['id_declaracion_jurada' => $ddjj->id]));
        }

        return redirect()->route('ddjj.show', $ddjj)->with('success', 'DDJJ actualizada.');
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
}
