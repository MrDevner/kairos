<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Pais;
use App\Models\RolInstitucion;
use App\Models\TipoLicencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->permisos()->administrador()->tieneTodosLosPermisos(), 403);
            return $next($request);
        })->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $instituciones = Institucion::with('padre')
                ->where(function ($q) use ($b) {
                    $q->where('nombre', 'like', "%{$b}%")
                      ->orWhere('sigla', 'like', "%{$b}%");
                })
                ->orderBy('nombre')
                ->paginate(25)
                ->withQueryString();
            return view('instituciones.index', compact('instituciones'));
        }

        $arbol = Institucion::arbol();
        return view('instituciones.index', compact('arbol'));
    }

    public function create(): View
    {
        $padres = Institucion::activas()->orderBy('nombre')->get();
        $paises = Pais::orderBy('nombre')->get(['id', 'nombre']);
        return view('instituciones.create', compact('padres', 'paises'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['configuracion'] = $this->extraerConfig($request);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Institucion::create($data);
        return redirect()->route('instituciones.index')->with('success', 'Institución creada.');
    }

    public function show(Institucion $institucion): View
    {
        $institucion->load([
            'padre',
            'hijas',
            'dependencias'      => fn ($q) => $q->raices()->orderBy('nombre'),
            'tiposLicenciaAviso',
        ]);

        $rolesInst = RolInstitucion::where('activo', true)->orderBy('nombre')->get();

        // Tipos visibles para la institución (para el selector de configuración de avisos)
        $tiposLicenciaDisponibles = TipoLicencia::visiblesParaInstitucion($institucion->id)
            ->activos()
            ->orderBy('nombre')
            ->get();

        $tiposLicenciaAvisoIds = $institucion->tiposLicenciaAviso->pluck('id');

        return view('instituciones.show', compact(
            'institucion', 'rolesInst',
            'tiposLicenciaDisponibles', 'tiposLicenciaAvisoIds'
        ));
    }

    public function edit(Institucion $institucion): View
    {
        $institucion->load('ciudadDomicilio.estado');
        $padres = Institucion::activas()
            ->where('id', '!=', $institucion->id)
            ->whereNotIn('id', $institucion->idsConDescendientes())
            ->orderBy('nombre')->get();
        $paises = Pais::orderBy('nombre')->get(['id', 'nombre']);
        return view('instituciones.edit', compact('institucion', 'padres', 'paises'));
    }

    public function update(Request $request, Institucion $institucion): RedirectResponse
    {
        $data = $this->validar($request);
        $config = $this->extraerConfig($request);
        // Preservar roles_autorizan_licencias porque se gestiona desde show(), no desde el form de edición
        $config['roles_autorizan_licencias'] = $institucion->configuracion['roles_autorizan_licencias'] ?? [];
        $data['configuracion'] = $config;

        if ($request->hasFile('logo')) {
            if ($institucion->logo) {
                Storage::disk('public')->delete($institucion->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        } elseif ($request->boolean('logo_eliminar') && $institucion->logo) {
            Storage::disk('public')->delete($institucion->logo);
            $data['logo'] = null;
        }

        $institucion->update($data);
        return redirect()->route('instituciones.show', $institucion)->with('success', 'Institución actualizada.');
    }

    public function destroy(Institucion $institucion): RedirectResponse
    {
        abort_if($institucion->hijas()->exists(), 422, 'No se puede eliminar una institución con sub-instituciones.');
        $institucion->delete();
        return redirect()->route('instituciones.index')->with('success', 'Institución eliminada.');
    }

    /**
     * Guarda los tipos de licencia habilitados para avisos en esta institución.
     * Accesible por Administrador General y administradores institucionales.
     */
    public function guardarAvisoLicencias(Request $request, Institucion $institucion): RedirectResponse
    {
        $actor  = auth()->user();
        $instId = $institucion->id;

        $esAdminGeneral = $actor->permisos()->administrador()->tieneTodosLosPermisos();
        $nivelActor     = $esAdminGeneral ? 0 : RolInstitucion::nivelMinimoDeUsuario($actor->id, $instId);

        abort_unless($esAdminGeneral || $nivelActor <= RolInstitucion::NIVEL_GESTION, 403);

        $ids = collect($request->input('tipos_licencia_aviso', []))
            ->map('intval')->filter()->unique()->values()->all();

        $request->validate([
            'tipos_licencia_aviso'   => ['array'],
            'tipos_licencia_aviso.*' => ['integer', 'exists:tipos_licencia,id'],
        ]);

        $institucion->tiposLicenciaAviso()->sync($ids);

        return back()->with('success', 'Tipos de licencia para avisos actualizados.');
    }

    /**
     * Guarda los roles adicionales que pueden autorizar licencias en esta institución.
     * Solo el Administrador General puede modificar esta configuración.
     */
    public function guardarAutorizadoresLicencias(Request $request, Institucion $institucion): RedirectResponse
    {
        abort_unless(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos(), 403);

        $ids = collect($request->input('roles_autorizan_licencias', []))
            ->map('intval')
            ->filter()
            ->values()
            ->all();

        // Validar que los IDs pertenecen a roles existentes
        $request->merge(['roles_autorizan_licencias' => $ids]);
        $request->validate([
            'roles_autorizan_licencias'   => ['array'],
            'roles_autorizan_licencias.*' => ['integer', 'exists:roles_institucion,id'],
        ]);

        $config                               = $institucion->configuracion;
        $config['roles_autorizan_licencias']  = $ids;
        $institucion->update(['configuracion' => $config]);

        return back()->with('success', 'Configuración de autorizadores actualizada.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'sigla'                => ['nullable', 'string', 'max:20'],
            'descripcion'          => ['nullable', 'string'],
            'id_institucion_padre' => ['nullable', 'integer', 'exists:instituciones,id'],
            'direccion'            => ['nullable', 'string', 'max:255'],
            'id_ciudad_domicilio'  => ['nullable', 'integer', 'exists:ciudades,id'],
            'telefono'             => ['nullable', 'string', 'max:30'],
            'email'                => ['nullable', 'email', 'max:150'],
            'activa'               => ['boolean'],
            'logo'                 => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        unset($data['logo']); // handled separately in store/update
        return $data;
    }

    private function extraerConfig(Request $request): array
    {
        return [
            'umbral_jornada_minima'   => (int) $request->input('cfg_umbral_jornada_minima', 60),
            'banco_horas_por'         => $request->input('cfg_banco_horas_por', 'usuario'),
            'permite_avisos_usuario'  => $request->boolean('cfg_permite_avisos_usuario'),
            'horas_extra_autorizadas' => $request->boolean('cfg_horas_extra_autorizadas'),
        ];
    }
}
