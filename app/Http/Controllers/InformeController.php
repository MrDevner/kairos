<?php

namespace App\Http\Controllers;

use App\Models\InformeDiario;
use App\Models\Institucion;
use App\Services\InformeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class InformeController extends Controller
{
    public function __construct(private readonly InformeService $service) {}

    public function index(Request $request): View
    {
        $instId = $request->filled('institucion')
            ? (int) $request->institucion
            : (int) session('institucion_activa_id', 0);

        $query = InformeDiario::orderByDesc('fecha')
            ->with('institucion', 'generadoPor');

        if ($instId) {
            $query->deInstitucion($instId);
        }

        $informes      = $query->paginate(20)->withQueryString();
        $instituciones = Institucion::activas()->orderBy('nombre')->get();

        return view('informes.index', compact('informes', 'instituciones', 'instId'));
    }

    public function generar(Request $request): RedirectResponse
    {
        $request->validate([
            'fecha'          => ['required', 'date'],
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id'],
        ]);

        $institucion = Institucion::findOrFail($request->id_institucion);
        $informe     = $this->service->generarInformeDiario($institucion, $request->fecha);

        return redirect()->route('informes.show', $informe)
            ->with('success', 'Informe generado correctamente.');
    }

    public function show(InformeDiario $informe): View
    {
        $informe->load([
            'institucion',
            'items.usuario',
            'items.designacion.cargo',
            'items.marcaComputada',
        ]);

        $resumen = [
            'presentes'    => $informe->items->where('tipo_novedad', 'presente')->count(),
            'tardanzas'    => $informe->items->where('tipo_novedad', 'tardanza')->count(),
            'ausencias'    => $informe->items->whereIn('tipo_novedad', ['ausencia_justificada','ausencia_injustificada'])->count(),
            'urgentes'     => $informe->items->where('requiere_atencion', true)->count(),
            'licencias'    => $informe->items->where('tipo_novedad', 'licencia')->count(),
        ];

        return view('informes.show', compact('informe', 'resumen'));
    }

    public function exportarExcel(InformeDiario $informe): BinaryFileResponse
    {
        $ruta = $this->service->exportarExcel($informe);
        $nombre = 'informe_' . $informe->fecha->format('Y-m-d') . '_' . $informe->institucion->sigla . '.xlsx';
        return response()->download($ruta, $nombre)->deleteFileAfterSend();
    }

    public function exportarPdf(InformeDiario $informe): BinaryFileResponse
    {
        $ruta = $this->service->exportarPDF($informe);
        $nombre = 'informe_' . $informe->fecha->format('Y-m-d') . '_' . $informe->institucion->sigla . '.pdf';
        return response()->download($ruta, $nombre)->deleteFileAfterSend();
    }
}
