<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_informe', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_informe_diario')
                ->constrained('informes_diarios')
                ->cascadeOnDelete();

            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->constrained('designaciones')
                ->cascadeOnDelete();

            $table->foreignId('id_marca_computada')
                ->nullable()
                ->constrained('marcas_computadas')
                ->nullOnDelete();

            $table->enum('tipo_novedad', [
                'presente',
                'ausencia_justificada',
                'ausencia_injustificada',
                'tardanza',
                'licencia',
                'feriado',
                'suspension',
                'error_atencion_urgente',
            ]);

            $table->text('detalle')->nullable();
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->integer('minutos_trabajados')->default(0);
            $table->string('razon_ausencia')->nullable();
            $table->boolean('requiere_atencion')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_informe');
    }
};
