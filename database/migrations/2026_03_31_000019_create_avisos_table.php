<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->constrained('designaciones')
                ->cascadeOnDelete();

            $table->foreignId('id_institucion')
                ->constrained('instituciones')
                ->cascadeOnDelete();

            // Fecha en la que se dio el aviso (puede diferir de la fecha del evento)
            $table->date('fecha_aviso');

            $table->enum('tipo', ['ausencia', 'tardanza']);

            // Fecha en la que ocurre la ausencia/tardanza
            $table->date('fecha_evento');

            $table->time('hora_estimada_llegada')->nullable();
            $table->text('motivo')->nullable();

            $table->foreignId('id_tipo_licencia')
                ->nullable()
                ->constrained('tipos_licencia')
                ->nullOnDelete();

            $table->foreignId('id_registrado_por')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }
};
