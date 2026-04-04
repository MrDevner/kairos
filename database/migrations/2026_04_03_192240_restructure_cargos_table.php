<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            // Agregar índice salarial por hora reloj
            $table->decimal('indice', 8, 4)->nullable()->after('horas_semanales')
                ->comment('Índice salarial por hora reloj');

            // Eliminar FK de institución antes de borrar la columna
            $table->dropForeign(['id_institucion']);
        });

        Schema::table('cargos', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'horas_mensuales', 'tipo', 'id_institucion']);
        });
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropColumn('indice');
            $table->text('descripcion')->nullable();
            $table->decimal('horas_mensuales', 6, 2)->nullable();
            $table->enum('tipo', ['cargo', 'horas_catedra'])->default('cargo');
            $table->unsignedBigInteger('id_institucion')->nullable();
        });
    }
};
