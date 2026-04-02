<?php

namespace App\Services;

use App\Models\Designacion;
use App\Models\Dispositivo;
use App\Models\Institucion;
use App\Models\MarcaComputada;
use App\Models\MarcaOriginal;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarcaService
{
    public function __construct(
        private readonly CalendarioService $calendarioService,
        private readonly LicenciaService   $licenciaService,
    ) {}

    /**
     * Procesa todas las marcas originales no procesadas de una fecha,
     * opcionalmente filtrando por institución.
     */
    public function procesarMarcasOriginales(
        string|Carbon $fecha,
        ?Institucion $institucion = null
    ): void {
        $fecha = Carbon::parse($fecha);

        $query = MarcaOriginal::noProcesadas()
            ->enFecha($fecha)
            ->with(['usuario.designaciones' => fn ($q) => $q->vigente()->with('cargo', 'institucion')])
            ->orderBy('id_usuario')
            ->orderBy('fecha_hora');

        if ($institucion) {
            $query->whereHas('dispositivo', fn ($q) => $q->where('id_institucion', $institucion->id));
        }

        $marcas = $query->get();

        // Agrupar por usuario
        $porUsuario = $marcas->groupBy('id_usuario');

        DB::transaction(function () use ($porUsuario, $fecha) {
            foreach ($porUsuario as $idUsuario => $marcasUsuario) {
                $this->procesarMarcasDeUsuario($idUsuario, $marcasUsuario, $fecha);
            }
        });
    }

    /**
     * Procesa las marcas de un usuario en una fecha, cruzando con DDJJ,
     * calendario y licencias para calcular la marca computada.
     */
    private function procesarMarcasDeUsuario(
        int $idUsuario,
        Collection $marcas,
        Carbon $fecha
    ): void {
        $usuario = $marcas->first()->usuario;

        foreach ($usuario->designaciones as $designacion) {
            $this->computarMarca($usuario, $designacion, $marcas, $fecha);
        }

        // Marcar todas las originales como procesadas
        MarcaOriginal::whereIn('id', $marcas->pluck('id'))->update(['procesada' => true]);
    }

    private function computarMarca(
        Usuario $usuario,
        Designacion $designacion,
        Collection $marcasOriginales,
        Carbon $fecha
    ): void {
        /** @var MarcaComputada $marca */
        $marca = MarcaComputada::firstOrNew([
            'id_usuario'    => $usuario->id,
            'id_designacion' => $designacion->id,
            'fecha'         => $fecha->toDateString(),
        ]);

        // Limpiar estado anterior si se reprocesa
        $marca->errores          = [];
        $marca->observaciones    = [];
        $marca->tiene_error      = false;
        $marca->tiene_observacion = false;

        // ── 1. Verificar licencia vigente ──────────────────────────────────
        $licencias = $this->licenciaService->licenciasVigentes($usuario, $fecha);
        if ($licencias->isNotEmpty()) {
            $marca->tipo                  = 'licencia';
            $marca->minutos_obligatorios  = 0;
            $marca->minutos_trabajados    = 0;
            $marca->minutos_extra         = 0;
            $marca->minutos_faltantes     = 0;
            $marca->agregarObservacion('Licencia vigente: ' . $licencias->first()->tipoLicencia->nombre);
            $marca->save();
            return;
        }

        // ── 2. Verificar calendario ────────────────────────────────────────
        $jornada = $this->calendarioService->calcularJornadaEfectiva($usuario, $designacion, $fecha);

        if (empty($jornada['horarios'])) {
            // Feriado, suspensión total u otro evento que elimina la jornada
            $eventos = $this->calendarioService->obtenerEventosFecha($designacion->institucion, $fecha);
            $tipoMarca = 'sin_obligacion';

            foreach ($eventos as $evento) {
                if (in_array($evento->tipo, ['feriado', 'dia_no_laborable'])) {
                    $tipoMarca = 'feriado';
                    break;
                }
                if ($evento->tipo === 'suspension_total') {
                    $tipoMarca = 'suspension';
                    break;
                }
            }

            // ¿El usuario marcó de todas formas? Observación de trabajo en feriado
            if ($marcasOriginales->isNotEmpty() && $tipoMarca === 'feriado') {
                $marca->agregarObservacion('El usuario marcó en un día feriado.');
            }

            $marca->tipo                 = $tipoMarca;
            $marca->minutos_obligatorios = 0;
            $marca->minutos_trabajados   = 0;
            $marca->minutos_extra        = 0;
            $marca->minutos_faltantes    = 0;
            $marca->save();
            return;
        }

        // ── 3. Calcular minutos obligatorios de la jornada efectiva ───────
        $minutosObligatorios = array_sum(array_map(function ($h) {
            [$hE, $mE] = explode(':', $h['hora_entrada']);
            [$hS, $mS] = explode(':', $h['hora_salida']);
            return ((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE);
        }, $jornada['horarios']));

        $marca->minutos_obligatorios = $minutosObligatorios;

        if ($jornada['requiere_revision']) {
            $marca->agregarObservacion('Jornada efectiva por debajo del umbral mínimo institucional. Requiere revisión.');
        }

        // ── 4. Determinar entrada y salida desde marcas originales ────────
        if ($marcasOriginales->isEmpty()) {
            $marca->tipo              = 'ausencia';
            $marca->minutos_trabajados = 0;
            $marca->minutos_extra     = 0;
            $marca->minutos_faltantes = $minutosObligatorios;
            $marca->agregarError('Sin marcas para el día. No se encontró justificación.');
            $marca->save();
            return;
        }

        $primeraEntrada = $marcasOriginales->sortBy('fecha_hora')->first();
        $ultimaSalida   = $marcasOriginales->sortByDesc('fecha_hora')->first();

        $marca->hora_entrada              = $primeraEntrada->fecha_hora->format('H:i:s');
        $marca->hora_salida               = $ultimaSalida->fecha_hora->format('H:i:s');
        $marca->id_marca_original_entrada = $primeraEntrada->id;
        $marca->id_marca_original_salida  = $ultimaSalida->id !== $primeraEntrada->id
            ? $ultimaSalida->id : null;

        // ── 5. Calcular minutos trabajados ─────────────────────────────────
        $entradaMin = $primeraEntrada->fecha_hora->hour * 60 + $primeraEntrada->fecha_hora->minute;
        $salidaMin  = $ultimaSalida->fecha_hora->hour * 60 + $ultimaSalida->fecha_hora->minute;
        $minutosTrabajados = max(0, $salidaMin - $entradaMin);

        $marca->minutos_trabajados = $minutosTrabajados;

        // ── 6. Calcular extra / faltantes ──────────────────────────────────
        $diferencia = $minutosTrabajados - $minutosObligatorios;

        if ($diferencia >= 0) {
            $marca->minutos_extra     = $diferencia;
            $marca->minutos_faltantes = 0;
        } else {
            $marca->minutos_extra     = 0;
            $marca->minutos_faltantes = abs($diferencia);
        }

        // ── 7. Determinar tipo de marca ────────────────────────────────────
        $horaObligatoriaEntrada = $jornada['horarios'][0]['hora_entrada'] ?? null;

        if ($marca->minutos_faltantes > 0 && $horaObligatoriaEntrada) {
            $llegadaMin   = $entradaMin;
            $obligatorioMin = (function ($t) {
                [$h, $m] = explode(':', $t);
                return (int)$h * 60 + (int)$m;
            })($horaObligatoriaEntrada);

            $marca->tipo = $llegadaMin > $obligatorioMin ? 'tardanza' : 'normal';
        } else {
            $marca->tipo = 'normal';
        }

        // Observación si hay tiempo extra
        if ($marca->minutos_extra > 0) {
            $marca->agregarObservacion("Tiempo extra: {$marca->minutos_extra} minutos.");
        }

        $marca->save();
    }

    /**
     * Importa marcas desde un archivo de texto/CSV para un dispositivo.
     * Formato esperado: una línea por marca con "documento,fecha_hora"
     * (ej: "12345678,2026-03-31 08:05:00")
     *
     * @throws \RuntimeException
     */
    public function importarMarcas(string $rutaArchivo, Dispositivo $dispositivo): array
    {
        if (!file_exists($rutaArchivo)) {
            throw new \RuntimeException("Archivo no encontrado: {$rutaArchivo}");
        }

        $lineas   = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $importadas = 0;
        $errores    = [];

        DB::transaction(function () use ($lineas, $dispositivo, &$importadas, &$errores) {
            foreach ($lineas as $nro => $linea) {
                $partes = str_getcsv($linea);

                if (count($partes) < 2) {
                    $errores[] = "Línea " . ($nro + 1) . ": formato inválido.";
                    continue;
                }

                [$documento, $fechaHoraRaw] = $partes;

                $usuario = Usuario::where('documento', trim($documento))->first();

                if (!$usuario) {
                    $errores[] = "Línea " . ($nro + 1) . ": documento '{$documento}' no encontrado.";
                    continue;
                }

                try {
                    $fechaHora = Carbon::parse(trim($fechaHoraRaw));
                } catch (\Exception) {
                    $errores[] = "Línea " . ($nro + 1) . ": fecha/hora inválida '{$fechaHoraRaw}'.";
                    continue;
                }

                MarcaOriginal::create([
                    'id_usuario'    => $usuario->id,
                    'id_dispositivo' => $dispositivo->id,
                    'fecha_hora'    => $fechaHora,
                    'tipo_captura'  => 'importada',
                    'datos_raw'     => ['linea_original' => $linea],
                    'procesada'     => false,
                ]);

                $importadas++;
            }
        });

        return ['importadas' => $importadas, 'errores' => $errores];
    }
}
