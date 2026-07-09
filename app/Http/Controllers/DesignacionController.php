<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignacionController extends Controller
{
    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $query  = Designacion::with(['usuario', 'cargo', 'institucion', 'dependencia'])
            ->orderByDesc('fecha_inicio');

        // Filtro automático por institución activa (salvo que el request lo sobreescriba)
        if ($instId && !$request->filled('institucion')) {
            $query->porInstitucion($instId);
        } elseif ($request->filled('institucion')) {
            $query->porInstitucion((int) $request->institucion);
        }

        if ($request->filled('dependencia')) {
            $query->where('id_dependencia', (int) $request->dependencia);
        }
        if ($request->filled('usuario')) {
            $query->where('id_usuario', (int) $request->usuario);
        }
        if ($request->filled('vigente')) {
            $query->vigente();
        }

        $designaciones = $query->paginate(25)->withQueryString();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('designaciones.index', compact('designaciones', 'instituciones'));
    }

    public function create(): View
    {
        $usuarios      = User::where('activo', true)->orderBy('apellidos')->get();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $cargos        = Cargo::activos()->orderBy('nombre')->get();
        $dependencias  = Dependencia::dependenciasActivas()->orderBy('nombre')->get();
        return view('designaciones.create', compact('usuarios', 'instituciones', 'cargos', 'dependencias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $des  = Designacion::create($data);
        try { $des->validarHoras(); } catch (\InvalidArgumentException $e) {
            $des->delete();
            return back()->withErrors(['horas_semanales_efectivas' => $e->getMessage()])->withInput();
        }
        return redirect()->route('designaciones.show', $des)->with('success', 'Designación creada.');
    }

    public function show(Designacion $designacion): View
    {
        $designacion->load(['usuario', 'cargo', 'institucion', 'dependencia', 'declaracionesJuradas']);
        return view('designaciones.show', compact('designacion'));
    }

    public function edit(Designacion $designacion): View
    {
        $usuarios      = User::where('activo', true)->orderBy('apellidos')->get();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $cargos        = Cargo::activos()->orderBy('nombre')->get();
        $dependencias  = Dependencia::dependenciasActivas()->orderBy('nombre')->get();
        return view('designaciones.edit', compact('designacion', 'usuarios', 'instituciones', 'cargos', 'dependencias'));
    }

    public function update(Request $request, Designacion $designacion): RedirectResponse
    {
        $data = $this->validar($request);
        $designacion->fill($data);
        try { $designacion->validarHoras(); } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['horas_semanales_efectivas' => $e->getMessage()])->withInput();
        }
        $designacion->save();
        return redirect()->route('designaciones.show', $designacion)->with('success', 'Designación actualizada.');
    }

    public function destroy(Designacion $designacion): RedirectResponse
    {
        $designacion->update(['activa' => false, 'fecha_fin' => now()->toDateString()]);
        return redirect()->route('designaciones.index')->with('success', 'Designación finalizada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'id_usuario'                => ['required', 'integer', 'exists:users,id'],
            'id_cargo'                  => ['required', 'integer', 'exists:cargos,id'],
            'id_institucion'            => ['required', 'integer', 'exists:instituciones,id'],
            'id_dependencia'            => ['required', 'integer', 'exists:dependencias,id'],
            'fecha_inicio'              => ['required', 'date'],
            'fecha_fin'                 => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'resolucion'                => ['nullable', 'string', 'max:100'],
            'horas_semanales_efectivas' => ['nullable', 'numeric', 'min:0.5', 'max:80'],
            'activa'                    => ['boolean'],
        ]);
    }
}
