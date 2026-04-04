<?php

namespace App\Services;

use App\Models\CondicionEvento;
use App\Models\Designacion;
use App\Models\EventoCalendario;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalendarioService
{
    /**
     * Devuelve todos los eventos de una institución en una fecha dada.
     *
     * @return Collection<int, EventoCalendario>
     */
    public function obtenerEventosFecha(Institucion $institucion, string|Carbon $fecha): Collection
    {
        $fecha = Carbon::parse($fecha)->toDateString();

        return EventoCalendario::deInstitucion($institucion->id)
            ->enFecha($fecha)
            ->with('condiciones')
            ->get();
    }

    /**
     * Determina si la fecha es hábil para la institución
     * (no hay feriado, suspensión total ni día no laborable).
     */
    public function esHabil(Institucion $institucion, string|Carbon $fecha): bool
    {
        $fecha = Carbon::parse($fecha);

        // Fin de semana siempre inhábil
        if ($fecha->isWeekend()) {
            return false;
        }

        return !EventoCalendario::deInstitucion($institucion->id)
            ->enFecha($fecha->toDateString())
            ->whereIn('tipo', ['feriado', 'suspension_total', 'dia_no_laborable'])
            ->exists();
    }

    /**
     * Calcula la jornada efectiva del usuario en una fecha, considerando eventos
     * del calendario institucional y sus condiciones.
     *
     * Retorna un array con:
     *   - 'horarios': bloques ajustados del día (array de {hora_entrada, hora_salida})
     *   - 'requiere_revision': bool — true si la jornada efectiva <= umbral_jornada_minima
     *
     * @return array{horarios: array, requiere_revision: bool}
     */
    public function calcularJornadaEfectiva(
        Usuario $usuario,
        Designacion $designacion,
        string|Carbon $fecha
    ): array {
        $fecha = Carbon::parse($fecha);

        if (!$this->esHabil($designacion->institucion, $fecha->toDateString())) {
            return ['horarios' => [], 'requiere_revision' => false];
        }

        $diaKey = mb_strtolower($fecha->locale('es')->isoFormat('dddd'));

        // Horarios declarados para ese día en la DDJJ aprobada vigente
        $ddjjVigente = $designacion->declaracionesJuradas()
            ->where('estado', 'aprobada')
            ->activas()
            ->with('horarios')
            ->latest('fecha_inicio')
            ->first();

        if (!$ddjjVigente) {
            return ['horarios' => [], 'requiere_revision' => false];
        }

        $horariosDia = $ddjjVigente->horarios
            ->filter(fn ($h) => $h->dia_semana === $diaKey)
            ->values();

        if ($horariosDia->isEmpty()) {
            return ['horarios' => [], 'requiere_revision' => false];
        }

        // Eventos de la institución en esa fecha con condiciones
        $eventos = $this->obtenerEventosFecha($designacion->institucion, $fecha);

        // Aplicar condiciones que afectan al usuario
        $condicionesAplicables = $eventos
            ->where('afecta_computo', true)
            ->flatMap(fn (EventoCalendario $e) => $e->condiciones)
            ->filter(fn (CondicionEvento $c) => $c->aplicaA($usuario, $designacion));

        $horariosAjustados = $horariosDia->map(function ($bloque) use ($condicionesAplicables) {
            $entrada = $bloque->hora_entrada;
            $salida  = $bloque->hora_salida;

            foreach ($condicionesAplicables as $condicion) {
                [$h, $m] = explode(':', $salida);
                $salidaMin = (int)$h * 60 + (int)$m;
                [$h, $m] = explode(':', $entrada);
                $entradaMin = (int)$h * 60 + (int)$m;

                switch ($condicion->efecto) {
                    case 'retiro_anticipado':
                        $salidaMin -= (int)$condicion->minutos_afectados;
                        $salida = sprintf('%02d:%02d', intdiv($salidaMin, 60), $salidaMin % 60);
                        break;
                    case 'ingreso_tardio':
                        $entradaMin += (int)$condicion->minutos_afectados;
                        $entrada = sprintf('%02d:%02d', intdiv($entradaMin, 60), $entradaMin % 60);
                        break;
                    case 'jornada_reducida':
                        $salidaMin -= (int)$condicion->minutos_afectados;
                        $salida = sprintf('%02d:%02d', intdiv($salidaMin, 60), $salidaMin % 60);
                        break;
                    case 'exencion':
                        // Jornada completa liberada
                        return null;
                }
            }

            return ['hora_entrada' => $entrada, 'hora_salida' => $salida];
        })->filter()->values()->all();

        // Calcular minutos efectivos totales
        $minutosEfectivos = array_sum(array_map(function ($h) {
            [$hE, $mE] = explode(':', $h['hora_entrada']);
            [$hS, $mS] = explode(':', $h['hora_salida']);
            return ((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE);
        }, $horariosAjustados));

        $umbral = $designacion->institucion->getConfig('umbral_jornada_minima');

        return [
            'horarios'          => $horariosAjustados,
            'requiere_revision' => $minutosEfectivos <= $umbral,
        ];
    }

    /**
     * Determina si hay un paro activo en la fecha que aplica al empleado dado.
     *
     * Un paro aplica si:
     *  - Existe un evento tipo 'paro' visible para la institución en esa fecha.
     *  - El evento no tiene filtros (condiciones) → aplica a todos.
     *  - O el empleado cumple al menos uno de los filtros definidos.
     */
    public function paroAplicaAEmpleado(
        Institucion $institucion,
        string|Carbon $fecha,
        Usuario $usuario,
        Designacion $designacion
    ): ?EventoCalendario {
        $fecha = Carbon::parse($fecha)->toDateString();

        $inst  = $institucion;
        $ids   = $inst->idsAncestoresYPropio();

        $paros = EventoCalendario::where('tipo', 'paro')
            ->enFecha($fecha)
            ->where(fn ($q) =>
                $q->whereNull('id_institucion')
                  ->orWhereIn('id_institucion', $ids)
            )
            ->with('condiciones')
            ->get();

        foreach ($paros as $paro) {
            // Sin filtros → aplica a todos
            if ($paro->condiciones->isEmpty()) {
                return $paro;
            }

            // Con filtros → debe cumplir al menos uno
            foreach ($paro->condiciones as $condicion) {
                if ($condicion->aplicaA($usuario, $designacion)) {
                    return $paro;
                }
            }
        }

        return null;
    }

    /**
     * Cuenta los días hábiles entre dos fechas (inclusive) para una institución.
     */
    public function diasHabilesEntre(
        Institucion $institucion,
        string|Carbon $fechaDesde,
        string|Carbon $fechaHasta
    ): int {
        $desde = Carbon::parse($fechaDesde);
        $hasta = Carbon::parse($fechaHasta);

        // Obtener fechas inhabilitadas en el rango de una sola consulta
        $fechasInhabiles = EventoCalendario::deInstitucion($institucion->id)
            ->whereIn('tipo', ['feriado', 'suspension_total', 'dia_no_laborable'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->pluck('fecha')
            ->map(fn ($f) => Carbon::parse($f)->toDateString())
            ->unique()
            ->all();

        $dias = 0;
        $cursor = $desde->copy();

        while ($cursor->lte($hasta)) {
            if (!$cursor->isWeekend() && !in_array($cursor->toDateString(), $fechasInhabiles)) {
                $dias++;
            }
            $cursor->addDay();
        }

        return $dias;
    }
}
