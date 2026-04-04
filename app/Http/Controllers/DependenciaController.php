<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Institucion;
use App\Models\Jefatura;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DependenciaController extends Controller
{
    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $query  = Dependencia::with(['institucion', 'padre'])->orderBy('nombre');

        if ($instId && !$request->filled('institucion')) {
            $query->deInstitucion($instId);
        } elseif ($request->filled('institucion')) {
            $query->deInstitucion((int) $request->institucion);
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('sigla', 'like', "%{$b}%");
            });
        }

        $dependencias  = $query->paginate(25)->withQueryString();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('dependencias.index', compact('dependencias', 'instituciones'));
    }

    public function create(): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $padres        = Dependencia::dependenciasActivas()->orderBy('nombre')->get();
        return view('dependencias.create', compact('instituciones', 'padres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activa'] = $request->boolean('activa');
        Dependencia::create($data);
        return redirect()->route('dependencias.index')->with('success', 'Dependencia creada.');
    }

    public function show(Dependencia $dependencia): View
    {
        $dependencia->load(['institucion', 'padre', 'hijos', 'jefaturas' => fn ($q) => $q->with('usuario')->latest('fecha_desde')]);
        $jefeActual = $dependencia->jefeActual();
        $usuarios   = Usuario::where('activo', true)->orderBy('apellidos')->orderBy('nombres')->get();
        return view('dependencias.show', compact('dependencia', 'jefeActual', 'usuarios'));
    }

    public function edit(Dependencia $dependencia): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        $excluidos     = $dependencia->idsConDescendientes();
        $padres        = Dependencia::dependenciasActivas()
            ->whereNotIn('id', $excluidos)
            ->orderBy('nombre')->get();
        return view('dependencias.edit', compact('dependencia', 'instituciones', 'padres'));
    }

    public function update(Request $request, Dependencia $dependencia): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activa'] = $request->boolean('activa');
        $dependencia->update($data);
        return redirect()->route('dependencias.show', $dependencia)->with('success', 'Dependencia actualizada.');
    }

    public function destroy(Dependencia $dependencia): RedirectResponse
    {
        abort_if($dependencia->hijos()->exists(), 422, 'No se puede eliminar una dependencia con sub-dependencias.');
        $dependencia->delete();
        return redirect()->route('dependencias.index')->with('success', 'Dependencia eliminada.');
    }

    public function asignarJefe(Request $request, Dependencia $dependencia): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario'  => ['required', 'integer', 'exists:usuarios,id'],
            'cargo'       => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        // Cerrar jefatura anterior
        Jefatura::where('id_dependencia', $dependencia->id)
            ->where('activa', true)
            ->update(['activa' => false, 'fecha_hasta' => now()->toDateString()]);

        Jefatura::create(array_merge($data, [
            'id_dependencia' => $dependencia->id,
            'activa'         => true,
        ]));

        return redirect()->route('dependencias.show', $dependencia)->with('success', 'Jefatura asignada.');
    }

    public function darDeBajaJefe(Dependencia $dependencia): RedirectResponse
    {
        $afectadas = Jefatura::where('id_dependencia', $dependencia->id)
            ->where('activa', true)
            ->update(['activa' => false, 'fecha_hasta' => now()->toDateString()]);

        if ($afectadas === 0) {
            return back()->with('warning', 'No hay jefatura activa para dar de baja.');
        }

        return redirect()->route('dependencias.show', $dependencia)
            ->with('success', 'Jefatura dada de baja correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'sigla'                => ['nullable', 'string', 'max:20'],
            'descripcion'          => ['nullable', 'string'],
            'id_institucion'       => ['required', 'integer', 'exists:instituciones,id'],
            'id_dependencia_padre' => ['nullable', 'integer', 'exists:dependencias,id'],
        ]);
    }
}
