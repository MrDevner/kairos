<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->nullable()
                ->constrained('designaciones')
                ->nullOnDelete();

            $table->foreignId('id_tipo_licencia')
                ->constrained('tipos_licencia')
                ->cascadeOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->integer('dias_computados')->nullable();

            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])
                ->default('pendiente');

            $table->text('motivo')->nullable();
            $table->string('documentacion')->nullable();

            $table->foreignId('id_registrado_por')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_aprobado_por')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->dateTime('fecha_aprobacion')->nullable();
            $table->text('observaciones_aprobacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencias');
    }
};
