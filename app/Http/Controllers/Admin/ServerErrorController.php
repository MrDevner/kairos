<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorServidor;
use App\Models\Usuario;
use App\Permisos\Permisos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerErrorController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->soloAdmin();

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'activos') === 'cerrados' ? 'cerrados' : 'activos';

        $desde = $request->input('desde', now()->subDays(30)->toDateString());
        $hasta = $request->input('hasta');

        $query = $tab === 'cerrados' ? ErrorServidor::cerrados() : ErrorServidor::activos();

        if ($request->filled('status')) {
            $query->where('estado', $request->status);
        }
        if ($request->filled('method')) {
            $query->where('metodo_http', $request->method);
        }
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('mensaje_error', 'like', "%{$q}%")
                    ->orWhere('endpoint', 'like', "%{$q}%")
                    ->orWhere('clase_error', 'like', "%{$q}%")
                    ->orWhere('id_correlacion', 'like', "%{$q}%");
            });
        }

        $errores = $query->orderByDesc('ultima_ocurrencia_en')->paginate(25)->withQueryString();
        $kpis = $this->calcularKpis();

        return view('admin.errores-servidor.index', compact('errores', 'tab', 'kpis'));
    }

    public function show(ErrorServidor $errorServidor): View
    {
        $errorServidor->load(['usuario', 'asignadoA']);
        $usuarios = Usuario::where('activo', true)->orderBy('apellidos')->orderBy('nombres')->get(['id', 'apellidos', 'nombres']);

        return view('admin.errores-servidor.show', compact('errorServidor', 'usuarios'));
    }

    public function update(Request $request, ErrorServidor $errorServidor): RedirectResponse
    {
        $data = $request->validate([
            'estado'        => ['nullable', 'in:abierto,en_revision,mitigado,solucionado'],
            'id_asignado_a' => ['nullable', 'integer', 'exists:usuarios,id'],
            'nota'          => ['nullable', 'string', 'max:2000'],
        ]);

        if (! empty($data['estado'])) {
            $errorServidor->estado = $data['estado'];

            if (in_array($data['estado'], ErrorServidor::ESTADOS_CERRADOS, true)) {
                $errorServidor->resuelto_en ??= now();
            } else {
                $errorServidor->resuelto_en = null;
            }
        }

        if ($request->has('id_asignado_a')) {
            $errorServidor->id_asignado_a = $data['id_asignado_a'] ?: null;
        }

        if (! empty($data['nota'])) {
            $errorServidor->agregarNota($request->user(), $data['nota']);
        }

        $errorServidor->save();

        return back()->with('success', 'Error actualizado.');
    }

    public function destroy(ErrorServidor $errorServidor): RedirectResponse
    {
        $errorServidor->delete();

        return redirect()->route('admin.errores-servidor.index')->with('success', 'Registro eliminado.');
    }

    private function soloAdmin(): void
    {
        abort_unless(Permisos::delUsuarioActual()->administrador()->tieneTodosLosPermisos(), 403);
    }

    private function calcularKpis(): array
    {
        $hace24h = now()->subDay();

        $ocurrencias24h = ErrorServidor::where('ultima_ocurrencia_en', '>=', $hace24h)->sum('cantidad_ocurrencias');

        $endpointTop = ErrorServidor::where('ultima_ocurrencia_en', '>=', $hace24h)
            ->whereNotNull('endpoint')
            ->select('endpoint')
            ->selectRaw('SUM(cantidad_ocurrencias) as total')
            ->groupBy('endpoint')
            ->orderByDesc('total')
            ->first();

        $mttrMinutos = ErrorServidor::whereNotNull('resuelto_en')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, resuelto_en)) as mttr')
            ->value('mttr');

        return [
            'total_abiertos'     => ErrorServidor::where('estado', 'abierto')->count(),
            'total_activos'      => ErrorServidor::activos()->count(),
            'ocurrencias_24h'    => (int) $ocurrencias24h,
            'endpoint_top'       => $endpointTop?->endpoint,
            'endpoint_top_total' => $endpointTop ? (int) $endpointTop->total : null,
            'mttr_minutos'       => $mttrMinutos !== null ? round((float) $mttrMinutos, 1) : null,
        ];
    }
}
