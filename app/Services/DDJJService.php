<?php

namespace App\Services;

use App\Models\DeclaracionJurada;
use App\Models\Designacion;
use App\Models\HorarioDdjj;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DDJJService
{
    /**
     * Valida que ninguno de los horarios propuestos se superponga con los
     * bloques de DDJJ activas del usuario (en todas sus designaciones).
     *
     * @param  array<array{dia_semana: string, hora_entrada: string, hora_salida: string}>  $horarios
     * @throws ValidationException
     */
    public function validarSuperposicion(
        User $usuario,
        array $horarios,
        ?DeclaracionJurada $excluirDdjj = null
    ): void {
        $query = DeclaracionJurada::deUsuario($usuario->id)->activas()
            ->with('horarios');

        if ($excluirDdjj) {
            $query->where('id', '!=', $excluirDdjj->id);
        }

        $horariosExistentes = $query->get()
            ->flatMap(fn (DeclaracionJurada $d) => $d->horarios);

        foreach ($horarios as $nuevo) {
            foreach ($horariosExistentes as $existente) {
                if ($existente->dia_semana !== $nuevo['dia_semana']) {
                    continue;
                }

                if ($nuevo['hora_entrada'] < $existente->hora_salida
                    && $existente->hora_entrada < $nuevo['hora_salida']
                ) {
                    throw ValidationException::withMessages([
                        'horarios' => "El horario del {$nuevo['dia_semana']} "
                            . "({$nuevo['hora_entrada']} - {$nuevo['hora_salida']}) "
                            . "se superpone con otro horario declarado.",
                    ]);
                }
            }
        }
    }

    /**
     * Valida que la suma de horas semanales declaradas no supere
     * las horas obligatorias de la designación.
     *
     * @param  array<array{hora_entrada: string, hora_salida: string}>  $horarios
     * @throws ValidationException
     */
    public function validarHorasMaximas(Designacion $designacion, array $horarios): void
    {
        $minutosDeclarados = collect($horarios)->sum(function (array $h) {
            [$hE, $mE] = explode(':', $h['hora_entrada']);
            [$hS, $mS] = explode(':', $h['hora_salida']);
            return ((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE);
        });

        $minutosObligatorios = $designacion->horasSemanalesObligatorias() * 60;

        if ($minutosDeclarados > $minutosObligatorios) {
            $horasDec = round($minutosDeclarados / 60, 2);
            $horasObl = round($minutosObligatorios / 60, 2);

            throw ValidationException::withMessages([
                'horarios' => "Las horas declaradas ({$horasDec}h) superan "
                    . "el máximo permitido por la designación ({$horasObl}h semanales).",
            ]);
        }
    }

    /**
     * Cambia el estado de la DDJJ a 'presentada', validando que sea un borrador
     * y que no exista otra DDJJ activa para la misma designación.
     *
     * @throws ValidationException|\LogicException
     */
    public function presentar(DeclaracionJurada $ddjj): void
    {
        if (!$ddjj->esBorrador()) {
            throw new \LogicException('Solo se puede presentar una DDJJ en estado borrador.');
        }

        // Verificar que no haya otra DDJJ activa para la misma designación
        $existeActiva = DeclaracionJurada::deDesignacion($ddjj->id_designacion)
            ->activas()
            ->where('id', '!=', $ddjj->id)
            ->exists();

        if ($existeActiva) {
            throw ValidationException::withMessages([
                'id_designacion' => 'Ya existe una declaración jurada activa para esta designación.',
            ]);
        }

        $ddjj->update(['estado' => 'presentada']);
    }

    /**
     * Aprueba una DDJJ presentada.
     *
     * @throws \LogicException
     */
    public function aprobar(DeclaracionJurada $ddjj, User $aprobadoPor): void
    {
        if (!$ddjj->estaPresentada()) {
            throw new \LogicException('Solo se puede aprobar una DDJJ presentada.');
        }

        $ddjj->update([
            'estado'       => 'aprobada',
            'observaciones' => $ddjj->observaciones
                . "\nAprobada por: {$aprobadoPor->nombre_completo} el " . now()->toDateTimeString(),
        ]);
    }
}
