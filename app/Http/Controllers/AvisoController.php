<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Designacion;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvisoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('ver', Aviso::class);

        $query = Aviso::with(['usuario', 'designacion.dependencia', 'registradoPor'])
            ->orderByDesc('fecha');

        if ($request->filled('dependencia')) {
            $query->deDependencia((int) $request->dependencia);
        }

        if ($request->filled('institucion')) {
            $query->deInstitucion((int) $request->institucion);
        }

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->enRango($request->fecha_desde, $request->fecha_hasta);
        } elseif ($request->filled('fecha')) {
            $query->enFecha($request->fecha);
        }

        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }

        // Limitar visibilidad según rol del usuario autenticado
        /** @var Usuario $auth */
        $auth = $request->user();

        if (!$auth->hasRole('Administrador General')) {
            $query->where(function ($q) use ($auth) {
                // Propio usuario
                $q->where('id_usuario', $auth->id)
                    // O instituciones donde tiene rol Departamento Personal
                    ->orWhereIn('id_institucion', function ($sub) use ($auth) {
                        $sub->select('id_institucion')
                            ->from('roles_institucion_usuario')
                            ->join('roles_institucion', 'roles_institucion.id', '=', 'roles_institucion_usuario.id_rol_institucion')
                            ->where('roles_institucion_usuario.id_usuario', $auth->id)
                            ->where('roles_institucion.nombre', 'Departamento Personal')
                            ->whereNull('roles_institucion_usuario.fecha_hasta')
                            ->orWhere('roles_institucion_usuario.fecha_hasta', '>=', now());
                    })
                    // O dependencias donde es jefe vigente
                    ->orWhereHas('designacion', function ($sub) use ($auth) {
                        $sub->whereIn('id_dependencia', function ($j) use ($auth) {
                            $j->select('id_dependencia')
                                ->from('jefaturas')
                                ->where('id_usuario', $auth->id)
                                ->where('activa', true)
                                ->where('fecha_desde', '<=', now())
                                ->where(function ($jq) {
                                    $jq->whereNull('fecha_hasta')->orWhere('fecha_hasta', '>=', now());
                                });
                        });
                    });
            });
        }

        $avisos = $query->paginate(25)->withQueryString();

        return view('avisos.index', compact('avisos'));
    }

    public function create(Request $request): View
    {
        $idInstitucion = (int) $request->input('id_institucion');
        $this->authorize('crear', [Aviso::class, $idInstitucion]);

        $instituciones = Institucion::activas()->get();
        $usuarios      = Usuario::where('activo', true)->orderBy('apellidos')->get();

        return view('avisos.create', compact('instituciones', 'usuarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario'             => ['required', 'integer', 'exists:usuarios,id'],
            'id_designacion'         => ['required', 'integer', 'exists:designaciones,id'],
            'id_institucion'         => ['required', 'integer', 'exists:instituciones,id'],
            'tipo'                   => ['required', 'in:ausencia,tardanza'],
            'fecha'                  => ['required', 'date'],
            'hora_estimada_llegada'  => ['nullable', 'date_format:H:i', 'required_if:tipo,tardanza'],
            'motivo'                 => ['nullable', 'string', 'max:1000'],
        ]);

        $this->authorize('crear', [Aviso::class, $data['id_institucion']]);

        /** @var Usuario $auth */
        $auth = $request->user();

        // Validar que el usuario pueda cargar su propio aviso si permite_avisos_usuario = false
        if ((int) $data['id_usuario'] === $auth->id) {
            $institucion = Institucion::findOrFail($data['id_institucion']);
            abort_unless($institucion->getConfig('permite_avisos_usuario'), 403);
        }

        Aviso::create(array_merge($data, ['id_registrado_por' => $auth->id]));

        return redirect()->route('avisos.index')->with('success', 'Aviso registrado correctamente.');
    }

    public function show(Aviso $aviso): View
    {
        $this->authorize('ver', $aviso);
        $aviso->load(['usuario', 'designacion.dependencia', 'institucion', 'registradoPor']);

        return view('avisos.show', compact('aviso'));
    }

    public function edit(Aviso $aviso): View
    {
        $this->authorize('editar', $aviso);
        return view('avisos.edit', compact('aviso'));
    }

    public function update(Request $request, Aviso $aviso): RedirectResponse
    {
        $this->authorize('editar', $aviso);

        $data = $request->validate([
            'tipo'                  => ['required', 'in:ausencia,tardanza'],
            'fecha'                 => ['required', 'date'],
            'hora_estimada_llegada' => ['nullable', 'date_format:H:i', 'required_if:tipo,tardanza'],
            'motivo'                => ['nullable', 'string', 'max:1000'],
        ]);

        $aviso->update($data);

        return redirect()->route('avisos.show', $aviso)->with('success', 'Aviso actualizado.');
    }

    public function destroy(Aviso $aviso): RedirectResponse
    {
        $this->authorize('eliminar', $aviso);
        $aviso->delete();

        return redirect()->route('avisos.index')->with('success', 'Aviso eliminado.');
    }
}
