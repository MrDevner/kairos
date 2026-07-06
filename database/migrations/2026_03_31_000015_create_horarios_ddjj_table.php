<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_ddjj', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_declaracion_jurada')
                ->constrained('declaraciones_juradas')
                ->cascadeOnDelete();

            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes']);
            $table->time('hora_entrada');
            $table->time('hora_salida');
            $table->enum('modalidad', ['presencial', 'remoto'])->default('presencial');

            $table->foreignId('id_institucion_externa')
                ->nullable()
                ->constrained('instituciones')
                ->nullOnDelete();

            $table->foreignId('id_dependencia')
                ->nullable()
                ->constrained('dependencias')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_ddjj');
    }
};
