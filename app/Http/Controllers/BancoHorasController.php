<?php

namespace App\Http\Controllers;

use App\Models\BancoHoras;
use App\Models\Designacion;
use App\Models\Usuario;
use App\Services\BancoHorasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BancoHorasController extends Controller
{
    public function __construct(private readonly BancoHorasService $service) {}

    public function index(Request $request): View
    {
        $instId = (int) session('institucion_activa_id', 0);

        $query = BancoHoras::with(['usuario', 'designacion.cargo', 'designacion.dependencia'])
            ->orderBy('saldo_minutos');

        if ($instId) {
            $query->whereHas('designacion', fn ($q) => $q->where('id_institucion', $instId));
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->whereHas('usuario', fn ($q) =>
                $q->where('nombres', 'like', "%{$b}%")
                  ->orWhere('apellidos', 'like', "%{$b}%")
                  ->orWhere('documento', 'like', "%{$b}%")
            );
        }

        $bancos = $query->paginate(25)->withQueryString();
        return view('banco-horas.index', compact('bancos'));
    }

    public function show(Usuario $usuario): View
    {
        $designaciones = $usuario->designaciones()->vigente()->with('cargo')->get();

        $bancos = BancoHoras::where('id_usuario', $usuario->id)
            ->with(['designacion.cargo', 'movimientos' => fn ($q) => $q->orderByDesc('fecha')->limit(50)])
            ->get();

        return view('banco-horas.show', compact('usuario', 'bancos', 'designaciones'));
    }

    public function ajuste(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_usuario'    => ['required', 'integer', 'exists:usuarios,id'],
            'id_designacion' => ['nullable', 'integer', 'exists:designaciones,id'],
            'minutos'       => ['required', 'integer', 'not_in:0'],
            'motivo'        => ['required', 'string', 'max:500'],
        ]);

        $usuario     = Usuario::findOrFail($data['id_usuario']);
        $designacion = isset($data['id_designacion'])
            ? Designacion::find($data['id_designacion'])
            : null;

        $banco = $this->service->obtenerOCrearBanco($usuario, $designacion);
        $this->service->ajusteManual($banco, $data['minutos'], $data['motivo'], $request->user());

        return redirect()->route('banco-horas.show', $usuario)
            ->with('success', 'Ajuste registrado. Nuevo saldo: ' . $banco->fresh()->saldo_minutos . ' min.');
    }
}
