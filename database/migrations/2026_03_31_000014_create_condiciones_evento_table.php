<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condiciones_evento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_evento_calendario')
                ->constrained('eventos_calendario')
                ->cascadeOnDelete();

            $table->enum('tipo_condicion', ['sexo', 'cargo', 'dependencia', 'custom']);
            $table->string('valor_condicion');

            $table->enum('efecto', [
                'retiro_anticipado',
                'ingreso_tardio',
                'jornada_reducida',
                'exencion',
            ]);

            $table->integer('minutos_afectados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_evento');
    }
};
