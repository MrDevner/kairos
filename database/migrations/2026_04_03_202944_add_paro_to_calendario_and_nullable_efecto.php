<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = DB::getTablePrefix();

        // Agregar 'paro' al enum tipo de eventos_calendario
        DB::statement("ALTER TABLE {$prefix}eventos_calendario MODIFY tipo ENUM(
            'feriado','suspension_total','suspension_parcial',
            'evento_condicional','dia_no_laborable','paro'
        ) NOT NULL");

        // Hacer efecto nullable en condiciones_evento
        // (los filtros de paro no tienen efecto)
        DB::statement("ALTER TABLE {$prefix}condiciones_evento MODIFY efecto ENUM(
            'retiro_anticipado','ingreso_tardio','jornada_reducida','exencion'
        ) NULL");
    }

    public function down(): void
    {
        $prefix = DB::getTablePrefix();

        DB::statement("ALTER TABLE {$prefix}eventos_calendario MODIFY tipo ENUM(
            'feriado','suspension_total','suspension_parcial',
            'evento_condicional','dia_no_laborable'
        ) NOT NULL");

        DB::statement("ALTER TABLE {$prefix}condiciones_evento MODIFY efecto ENUM(
            'retiro_anticipado','ingreso_tardio','jornada_reducida','exencion'
        ) NOT NULL");
    }
};
