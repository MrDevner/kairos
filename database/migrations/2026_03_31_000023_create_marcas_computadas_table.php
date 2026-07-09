<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas_computadas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->constrained('designaciones')
                ->cascadeOnDelete();

            $table->date('fecha');

            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();

            $table->foreignId('id_marca_original_entrada')
                ->nullable()
                ->constrained('marcas_originales')
                ->nullOnDelete();

            $table->foreignId('id_marca_original_salida')
                ->nullable()
                ->constrained('marcas_originales')
                ->nullOnDelete();

            $table->enum('tipo', [
                'normal',
                'tardanza',
                'ausencia',
                'licencia',
                'feriado',
                'suspension',
                'sin_obligacion',
            ])->default('normal');

            $table->integer('minutos_trabajados')->default(0);
            $table->integer('minutos_obligatorios')->default(0);
            $table->integer('minutos_extra')->default(0);
            $table->integer('minutos_faltantes')->default(0);

            $table->boolean('tiempo_extra_autorizado')->default(false);
            $table->boolean('tiene_error')->default(false);
            $table->boolean('tiene_observacion')->default(false);

            $table->json('errores')->nullable();
            $table->json('observaciones')->nullable();

            $table->timestamps();

            $table->unique(['id_usuario', 'id_designacion', 'fecha']);
            $table->index(['id_usuario', 'fecha']);
            $table->index('tiene_error');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas_computadas');
    }
};
