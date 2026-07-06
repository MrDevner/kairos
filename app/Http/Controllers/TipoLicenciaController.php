<?php

namespace App\Http\Controllers;

use App\Models\CategoriaCargo;
use App\Models\Institucion;
use App\Models\TipoLicencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipoLicenciaController extends Controller
{
    public function index(Request $request): View
    {
        $instId  = (int) session('institucion_activa_id', 0);
        $esAdmin = auth()->user()->permisos()->administrador()->tieneTodosLosPermisos();

        abort_unless($esAdmin || $instId, 403);

        $query = TipoLicencia::with(['institucion', 'categoriaCargo'])->orderBy('nombre');

        // Con institución activa: mostrar sólo tipos visibles para esa institución (propios + ancestros + globales)
        if ($instId && !$request->filled('institucion')) {
            $query->visiblesParaInstitucion($instId);
        } elseif ($request->filled('institucion')) {
            if ($request->institucion === 'global') {
                $query->whereNull('id_institucion');
            } else {
                $query->where('id_institucion', (int) $request->institucion);
            }
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$b}%")
                                       ->orWhere('descripcion', 'like', "%{$b}%"));
        }

        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->activo);
        }

        if ($request->filled('categoria')) {
            if ($request->categoria === 'todas') {
                $query->whereNull('id_categoria_cargo');
            } else {
                $query->where('id_categoria_cargo', (int) $request->categoria);
            }
        }

        $tipos         = $query->paginate(25)->withQueryString();
        $instituciones = $esAdmin ? Institucion::activas()->orderBy('nombre')->get() : collect();
        $categorias    = CategoriaCargo::orderBy('nombre')->get();

        return view('tipos-licencia.index', compact('tipos', 'instituciones', 'categorias'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        $listaInstituciones = Institucion::listaJerarquica();
        $categorias         = CategoriaCargo::activas()->orderBy('nombre')->get();
        return view('tipos-licencia.create', compact('listaInstituciones', 'categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        $data = $this->validar($request);
        $data['requiere_documentacion'] = $request->boolean('requiere_documentacion');
        $data['activo']                 = $request->boolean('activo');

        $tipo = TipoLicencia::create($data);
        return redirect()->route('tipos-licencia.show', $tipo)->with('success', 'Tipo de licencia creado.');
    }

    public function show(TipoLicencia $tipo): View
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        $tipo->load(['institucion', 'categoriaCargo']);
        return view('tipos-licencia.show', compact('tipo'));
    }

    public function edit(TipoLicencia $tipo): View
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        $listaInstituciones = Institucion::listaJerarquica();
        $categorias         = CategoriaCargo::activas()->orderBy('nombre')->get();
        return view('tipos-licencia.edit', compact('tipo', 'listaInstituciones', 'categorias'));
    }

    public function update(Request $request, TipoLicencia $tipo): RedirectResponse
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        $data = $this->validar($request, $tipo);
        $data['requiere_documentacion'] = $request->boolean('requiere_documentacion');
        $data['activo']                 = $request->boolean('activo');

        $tipo->update($data);
        return redirect()->route('tipos-licencia.show', $tipo)->with('success', 'Tipo de licencia actualizado.');
    }

    public function destroy(TipoLicencia $tipo): RedirectResponse
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        if ($tipo->licencias()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay licencias asociadas a este tipo.');
        }

        $tipo->delete();
        return redirect()->route('tipos-licencia.index')->with('success', 'Tipo de licencia eliminado.');
    }

    private function validar(Request $request, ?TipoLicencia $tipo = null): array
    {
        $uniqueNombre = 'unique:tipos_licencia,nombre' . ($tipo ? ",{$tipo->id}" : '');

        return $request->validate([
            'nombre'          => ['required', 'string', 'max:100', $uniqueNombre],
            'descripcion'     => ['nullable', 'string', 'max:1000'],
            'computo'         => ['required', 'in:dias_corridos,dias_habiles'],
            'afecta'          => ['required', 'in:usuario,designacion'],
            'dias_maximos'    => ['nullable', 'integer', 'min:1'],
            'id_institucion'      => ['nullable', 'integer', 'exists:instituciones,id'],
            'id_categoria_cargo'  => ['nullable', 'integer', 'exists:categorias_cargo,id'],
        ]);
    }
}
