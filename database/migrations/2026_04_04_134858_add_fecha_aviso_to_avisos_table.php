<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            // Renombrar 'fecha' → 'fecha_evento' (cuándo ocurre la tardanza/ausencia)
            $table->renameColumn('fecha', 'fecha_evento');
        });

        Schema::table('avisos', function (Blueprint $table) {
            // Agregar 'fecha_aviso' (cuándo se dio el aviso); retrocompatibilidad: igual a fecha_evento
            $table->date('fecha_aviso')->nullable()->after('id_institucion');
        });

        // Poblar fecha_aviso con el mismo valor que fecha_evento para registros existentes
        DB::table('avisos')->update(['fecha_aviso' => DB::raw('fecha_evento')]);

        // Ahora que está poblado, hacerla not-null
        Schema::table('avisos', function (Blueprint $table) {
            $table->date('fecha_aviso')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->dropColumn('fecha_aviso');
            $table->renameColumn('fecha_evento', 'fecha');
        });
    }
};
