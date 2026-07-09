<?php

namespace App\Services;

use App\Models\BancoHoras;
use App\Models\Designacion;
use App\Models\MarcaComputada;
use App\Models\MovimientoBancoHoras;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BancoHorasService
{
    /**
     * Obtiene o crea el banco de horas según la configuración de la institución.
     * Si banco_horas_por = 'usuario', id_designacion es null.
     * Si banco_horas_por = 'designacion', se usa la designación indicada.
     */
    public function obtenerOCrearBanco(
        User $usuario,
        ?Designacion $designacion = null
    ): BancoHoras {
        // Determinar si el banco es por usuario o por designación
        $idDesignacion = null;

        if ($designacion !== null) {
            $designacion->loadMissing('institucion');
            $porUsuario = $designacion->institucion->getConfig('banco_horas_por') === 'usuario';
            $idDesignacion = $porUsuario ? null : $designacion->id;
        }

        return BancoHoras::firstOrCreate(
            ['id_usuario' => $usuario->id, 'id_designacion' => $idDesignacion],
            ['saldo_minutos' => 0, 'autorizado_acumular' => false, 'autorizado_negativo' => false]
        );
    }

    /**
     * Acredita minutos extra al banco. Solo opera si autorizado_acumular = true.
     */
    public function acreditarExtra(
        BancoHoras $banco,
        int $minutos,
        ?MarcaComputada $marca = null
    ): void {
        if (!$banco->autorizado_acumular || $minutos <= 0) {
            return;
        }

        DB::transaction(function () use ($banco, $minutos, $marca) {
            $banco->increment('saldo_minutos', $minutos);

            MovimientoBancoHoras::create([
                'id_banco_horas'     => $banco->id,
                'fecha'              => $marca?->fecha ?? now()->toDateString(),
                'tipo'               => 'extra',
                'minutos'            => $minutos,
                'motivo'             => 'Acreditación automática de horas extra.',
                'id_marca_computada' => $marca?->id,
            ]);
        });
    }

    /**
     * Debita minutos faltantes del banco. Solo opera si autorizado_acumular = true.
     * Respeta autorizado_negativo: si el banco no puede ir negativo y
     * el débito lo dejaría en negativo, no opera.
     */
    public function debitarFaltante(
        BancoHoras $banco,
        int $minutos,
        ?MarcaComputada $marca = null
    ): void {
        if (!$banco->autorizado_acumular || $minutos <= 0) {
            return;
        }

        if (!$banco->autorizado_negativo && ($banco->saldo_minutos - $minutos) < 0) {
            return;
        }

        DB::transaction(function () use ($banco, $minutos, $marca) {
            $banco->decrement('saldo_minutos', $minutos);

            MovimientoBancoHoras::create([
                'id_banco_horas'     => $banco->id,
                'fecha'              => $marca?->fecha ?? now()->toDateString(),
                'tipo'               => 'faltante',
                'minutos'            => -$minutos,
                'motivo'             => 'Débito automático por horas faltantes.',
                'id_marca_computada' => $marca?->id,
            ]);
        });
    }

    /**
     * Ajuste manual del saldo (positivo o negativo).
     */
    public function ajusteManual(
        BancoHoras $banco,
        int $minutos,
        string $motivo,
        User $registradoPor
    ): void {
        DB::transaction(function () use ($banco, $minutos, $motivo, $registradoPor) {
            $banco->increment('saldo_minutos', $minutos);

            MovimientoBancoHoras::create([
                'id_banco_horas'    => $banco->id,
                'fecha'             => now()->toDateString(),
                'tipo'              => 'ajuste_manual',
                'minutos'           => $minutos,
                'motivo'            => $motivo,
                'id_registrado_por' => $registradoPor->id,
            ]);
        });
    }

    /**
     * Devuelve el saldo actual en minutos del banco del usuario,
     * respetando la lógica de banco por usuario o por designación.
     */
    public function consultarSaldo(User $usuario, ?Designacion $designacion = null): int
    {
        $banco = $this->obtenerOCrearBanco($usuario, $designacion);
        return $banco->saldo_minutos;
    }
}
