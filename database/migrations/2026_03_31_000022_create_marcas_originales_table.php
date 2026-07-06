<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas_originales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_dispositivo')
                ->constrained('dispositivos')
                ->cascadeOnDelete();

            $table->dateTime('fecha_hora');
            $table->enum('tipo_captura', ['automatica', 'importada', 'web']);
            $table->json('datos_raw')->nullable();
            $table->boolean('procesada')->default(false);
            $table->timestamps();

            $table->index(['id_usuario', 'fecha_hora']);
            $table->index('procesada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas_originales');
    }
};
