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

    // ── CRUD marcas_originales ─────────────────────────────────────────────

    public function index(Request $request): View
    {
        $desde = $request->input('desde', today()->toDateString());
        $hasta = $request->input('hasta', today()->toDateString());

        $query = MarcaOriginal::enRango($desde, $hasta)
            ->with(['usuario', 'dispositivo'])
            ->orderByDesc('fecha_hora');

        if ($request->filled('id_usuario')) {
            $query->deUsuario((int) $request->id_usuario);
        }
        if ($request->filled('tipo_captura')) {
            $query->where('tipo_captura', $request->tipo_captura);
        }
        if ($request->filled('procesada') && $request->procesada !== '') {
            $query->where('procesada', (bool) $request->procesada);
        }

        $marcas   = $query->paginate(50)->withQueryString();
        $usuarios = Usuario::orderBy('apellidos')->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'documento']);

        return view('marcas.index', compact('marcas', 'desde', 'hasta', 'usuarios'));
    }

    public function create(): View
    {
        $dispositivos = Dispositivo::activos()->orderBy('nombre')->get();
        $usuarios     = Usuario::orderBy('apellidos')->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'documento']);
        return view('marcas.create', compact('dispositivos', 'usuarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $data['tipo_captura'] = 'importada';
        $data['procesada']    = false;
        MarcaOriginal::create($data);
        return redirect()->route('marcas.index')->with('success', 'Marca registrada correctamente.');
    }

    public function show(MarcaOriginal $marca): View
    {
        $marca->load('usuario', 'dispositivo');
        $computadas = MarcaComputada::deUsuario($marca->id_usuario)
            ->enFecha($marca->fecha_hora->toDateString())
            ->with('designacion.cargo')
            ->get();
        return view('marcas.show', compact('marca', 'computadas'));
    }

    public function edit(MarcaOriginal $marca): View
    {
        $dispositivos = Dispositivo::activos()->orderBy('nombre')->get();
        $usuarios     = Usuario::orderBy('apellidos')->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'documento']);
        return view('marcas.edit', compact('marca', 'dispositivos', 'usuarios'));
    }

    public function update(Request $request, MarcaOriginal $marca): RedirectResponse
    {
        $data = $this->validar($request);
        $data['procesada'] = false;
        $marca->update($data);
        return redirect()->route('marcas.index')->with('success', 'Marca actualizada y marcada para reprocesar.');
    }

    public function destroy(MarcaOriginal $marca): RedirectResponse
    {
        $marca->delete();
        return back()->with('success', 'Marca eliminada.');
    }

    // ── Importación ────────────────────────────────────────────────────────

    public function importar(): View
    {
        $dispositivos = Dispositivo::activos()->with('institucion')->orderBy('nombre')->get();
        return view('marcas.importar', compact('dispositivos'));
    }

    public function procesarImportacion(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo'        => ['required', 'file', 'mimes:txt,csv,dat', 'max:10240'],
            'id_dispositivo' => ['required', 'integer', 'exists:dispositivos,id'],
        ]);

        $dispositivo = Dispositivo::findOrFail($request->id_dispositivo);
        $ruta        = $request->file('archivo')->getPathname();

        try {
            $resultado = $this->service->importarMarcas($ruta, $dispositivo);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = "Importación completada: {$resultado['importadas']} marcas importadas.";
        if (!empty($resultado['errores'])) {
            $msg .= ' Líneas con error: ' . count($resultado['errores']);
            return back()->with('warning', $msg)->with('errores_importacion', $resultado['errores']);
        }

        return redirect()->route('marcas.index')->with('success', $msg);
    }

    // ── Procesamiento ──────────────────────────────────────────────────────

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

        return redirect()->route('marcas.index', ['desde' => $request->fecha, 'hasta' => $request->fecha])
            ->with('success', 'Marcas procesadas correctamente.');
    }

    // ── Vista computadas (desde Informes) ──────────────────────────────────

    public function computadas(Request $request): View
    {
        $fecha  = $request->input('fecha', today()->toDateString());
        $instId = (int) session('institucion_activa_id', 0);

        $query = MarcaComputada::enFecha($fecha)
            ->with(['usuario', 'designacion.cargo'])
            ->orderBy('id_usuario');

        if ($instId) {
            $query->whereHas('designacion', fn ($q) => $q->porInstitucion($instId));
        }
        if ($request->filled('id_usuario')) {
            $query->deUsuario((int) $request->id_usuario);
        }
        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }

        $marcas   = $query->paginate(30)->withQueryString();
        $usuarios = Usuario::orderBy('apellidos')->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos']);

        return view('marcas.computadas', compact('marcas', 'fecha', 'usuarios'));
    }

    // ── Validación común ──────────────────────────────────────────────────

    private function validar(Request $request): array
    {
        return $request->validate([
            'id_usuario'     => ['required', 'integer', 'exists:usuarios,id'],
            'id_dispositivo' => ['required', 'integer', 'exists:dispositivos,id'],
            'fecha_hora'     => ['required', 'date'],
        ]);
    }
}
