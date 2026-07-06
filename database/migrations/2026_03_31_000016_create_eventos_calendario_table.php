<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_calendario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_institucion')
                ->nullable()
                ->constrained('instituciones')
                ->cascadeOnDelete();

            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->enum('tipo', [
                'feriado',
                'suspension_total',
                'suspension_parcial',
                'evento_condicional',
                'dia_no_laborable',
                'paro',
            ]);

            $table->time('hora_desde')->nullable();
            $table->time('hora_hasta')->nullable();
            $table->boolean('afecta_computo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_calendario');
    }
};
