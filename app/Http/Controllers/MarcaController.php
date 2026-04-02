<?php

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\Institucion;
use App\Models\MarcaComputada;
use App\Models\MarcaOriginal;
use App\Models\Usuario;
use App\Services\MarcaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MarcaController extends Controller
{
    public function __construct(private readonly MarcaService $service) {}

    public function index(Request $request): View
    {
        $fecha     = $request->input('fecha', now()->toDateString());
        $instId    = (int) session('institucion_activa_id', 0);

        $query = MarcaComputada::enFecha($fecha)
            ->with(['usuario', 'designacion.cargo'])
            ->orderBy('id_usuario');

        if ($instId) {
            $query->whereHas('designacion', fn ($q) => $q->porInstitucion($instId));
        }

        if ($request->filled('usuario')) {
            $query->deUsuario((int) $request->usuario);
        }
        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }

        $marcas = $query->paginate(30)->withQueryString();
        return view('marcas.index', compact('marcas', 'fecha'));
    }

    public function show(Usuario $usuario, string $fecha): View
    {
        $fechaCarbon = Carbon::parse($fecha);
        $marcas = MarcaComputada::deUsuario($usuario->id)
            ->enFecha($fechaCarbon)
            ->with('designacion.cargo', 'marcaEntrada.dispositivo', 'marcaSalida.dispositivo')
            ->get();

        $originales = MarcaOriginal::deUsuario($usuario->id)
            ->enFecha($fechaCarbon)
            ->with('dispositivo')
            ->orderBy('fecha_hora')
            ->get();

        return view('marcas.show', compact('usuario', 'fecha', 'marcas', 'originales'));
    }

    public function importar(): View
    {
        $dispositivos = Dispositivo::activos()
            ->where('modo_conexion', 'importacion')
            ->with('institucion')
            ->get();
        return view('marcas.importar', compact('dispositivos'));
    }

    public function procesarImportacion(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo'        => ['required', 'file', 'mimes:txt,csv', 'max:10240'],
            'id_dispositivo' => ['required', 'integer', 'exists:dispositivos,id'],
        ]);

        $dispositivo = Dispositivo::findOrFail($request->id_dispositivo);
        $ruta        = $request->file('archivo')->getPathname();

        try {
            $resultado = $this->service->importarMarcas($ruta, $dispositivo);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = "Importación completada: {$resultado['importadas']} marcas procesadas.";
        if (!empty($resultado['errores'])) {
            $msg .= ' Errores: ' . count($resultado['errores']);
            return back()->with('warning', $msg)->with('errores_importacion', $resultado['errores']);
        }

        return redirect()->route('marcas.index')->with('success', $msg);
    }

    public function procesar(Request $request): RedirectResponse
    {
        $request->validate([
            'fecha'          => ['required', 'date'],
            'id_institucion' => ['nullable', 'integer', 'exists:instituciones,id'],
        ]);

        $institucion = $request->filled('id_institucion')
            ? Institucion::find($request->id_institucion)
            : null;

        $this->service->procesarMarcasOriginales($request->fecha, $institucion);

        return redirect()->route('marcas.index', ['fecha' => $request->fecha])
            ->with('success', 'Marcas procesadas correctamente.');
    }
}
