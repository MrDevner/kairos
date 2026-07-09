<?php

namespace App\Services;

use App\Models\Institucion;
use App\Models\Licencia;
use App\Models\TipoLicencia;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class LicenciaService
{
    public function __construct(
        private readonly CalendarioService $calendarioService
    ) {}

    /**
     * Registra una nueva licencia. El registrador no puede ser el propio usuario.
     *
     * @throws ValidationException
     */
    public function registrar(array $datos, User $registradoPor): Licencia
    {
        if ((int) $datos['id_usuario'] === $registradoPor->id) {
            throw ValidationException::withMessages([
                'id_usuario' => 'Un usuario no puede registrar su propia licencia.',
            ]);
        }

        return Licencia::create(array_merge($datos, [
            'id_registrado_por' => $registradoPor->id,
            'estado'            => 'pendiente',
        ]));
    }

    /**
     * Aprueba una licencia y calcula los días según el tipo de cómputo.
     *
     * @throws \LogicException
     */
    public function aprobar(Licencia $licencia, User $aprobadoPor): void
    {
        if (!$licencia->estaPendiente()) {
            throw new \LogicException('Solo se pueden aprobar licencias pendientes.');
        }

        $licencia->loadMissing(['tipoLicencia', 'usuario.designaciones.institucion']);

        // Determinar la institución para el cómputo de días hábiles
        $institucion = $licencia->designacion?->institucion
            ?? $licencia->usuario->designaciones()->vigente()->first()?->institucion;

        $diasComputados = $this->calcularDias(
            $licencia->tipoLicencia,
            $licencia->fecha_inicio,
            $licencia->fecha_fin ?? $licencia->fecha_inicio,
            $institucion
        );

        $licencia->update([
            'estado'          => 'aprobada',
            'id_aprobado_por' => $aprobadoPor->id,
            'fecha_aprobacion' => now(),
            'dias_computados' => $diasComputados,
        ]);
    }

    /**
     * Rechaza una licencia con observaciones.
     *
     * @throws \LogicException
     */
    public function rechazar(Licencia $licencia, User $aprobadoPor, string $observaciones): void
    {
        if (!$licencia->estaPendiente()) {
            throw new \LogicException('Solo se pueden rechazar licencias pendientes.');
        }

        $licencia->update([
            'estado'                   => 'rechazada',
            'id_aprobado_por'          => $aprobadoPor->id,
            'fecha_aprobacion'         => now(),
            'observaciones_aprobacion' => $observaciones,
        ]);
    }

    /**
     * Calcula los días computados según el tipo de cómputo del tipo de licencia.
     * Si $institucion es null y el tipo es días_hábiles, se usa días corridos como fallback.
     */
    public function calcularDias(
        TipoLicencia $tipoLicencia,
        Carbon|string $fechaInicio,
        Carbon|string $fechaFin,
        ?Institucion $institucion = null
    ): int {
        $inicio = Carbon::parse($fechaInicio);
        $fin    = Carbon::parse($fechaFin);

        if ($tipoLicencia->esDiasCorridos() || $institucion === null) {
            return $inicio->diffInDays($fin) + 1;
        }

        return $this->calendarioService->diasHabilesEntre($institucion, $inicio, $fin);
    }

    /**
     * Devuelve las licencias aprobadas y vigentes de un usuario en una fecha.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function licenciasVigentes(User $usuario, string|Carbon $fecha)
    {
        return Licencia::deUsuario($usuario->id)
            ->vigentesEnFecha($fecha)
            ->with('tipoLicencia')
            ->get();
    }
}
