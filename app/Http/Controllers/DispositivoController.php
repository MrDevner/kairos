<?php

namespace App\Http\Controllers;

use App\Models\ComputadorAutorizado;
use App\Models\Dispositivo;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispositivoController extends Controller
{
    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);
        $query  = Dispositivo::with('institucion')->orderBy('nombre');

        if ($instId && !$request->filled('institucion')) {
            $query->deInstitucion($instId);
        } elseif ($request->filled('institucion')) {
            $query->deInstitucion((int) $request->institucion);
        }

        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('ubicacion', 'like', "%{$b}%");
            });
        }

        $dispositivos = $query->paginate(25)->withQueryString();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('dispositivos.index', compact('dispositivos', 'instituciones'));
    }

    public function create(): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        return view('dispositivos.create', compact('instituciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        Dispositivo::create($data);

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo creado correctamente.');
    }

    public function show(Dispositivo $dispositivo): View
    {
        $dispositivo->load('institucion');
        $computadores = $dispositivo->computadoresAutorizados()
            ->orderByDesc('autorizado')
            ->orderBy('nombre_equipo')
            ->get();

        return view('dispositivos.show', compact('dispositivo', 'computadores'));
    }

    public function autorizarComputador(Dispositivo $dispositivo, ComputadorAutorizado $computador): RedirectResponse
    {
        abort_unless($computador->id_dispositivo === $dispositivo->id, 404);

        $computador->update(['autorizado' => !$computador->autorizado]);

        $accion = $computador->autorizado ? 'autorizado' : 'revocado';
        return back()->with('success', "Terminal \"{$computador->nombre_equipo}\" {$accion}.");
    }

    public function eliminarComputador(Dispositivo $dispositivo, ComputadorAutorizado $computador): RedirectResponse
    {
        abort_unless($computador->id_dispositivo === $dispositivo->id, 404);

        $nombre = $computador->nombre_equipo;
        $computador->delete();

        return back()->with('success', "Terminal \"{$nombre}\" eliminado.");
    }

    public function edit(Dispositivo $dispositivo): View
    {
        $instituciones = Institucion::activas()->orderBy('nombre')->get();
        return view('dispositivos.edit', compact('dispositivo', 'instituciones'));
    }

    public function update(Request $request, Dispositivo $dispositivo): RedirectResponse
    {
        $data = $this->validar($request);
        $data['activo'] = $request->boolean('activo');
        $dispositivo->update($data);

        return redirect()->route('dispositivos.show', $dispositivo)
            ->with('success', 'Dispositivo actualizado.');
    }

    public function destroy(Dispositivo $dispositivo): RedirectResponse
    {
        $dispositivo->delete();

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre'        => ['required', 'string', 'max:255'],
            'ubicacion'     => ['required', 'string', 'max:255'],
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id'],
            'tipo'          => ['required', 'in:biometrico,web,otro'],
            'modo_conexion' => ['required', 'in:directo_bd,importacion,web'],
            'ip_address'    => ['nullable', 'ip'],
            'configuracion' => ['nullable', 'array'],
            'activo'        => ['boolean'],
        ]);
    }
}
