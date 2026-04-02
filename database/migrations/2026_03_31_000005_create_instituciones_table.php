<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('sigla');
            $table->text('descripcion')->nullable();

            // Auto-referencia: institución padre (nullable = es raíz)
            $table->foreignId('id_institucion_padre')
                ->nullable()
                ->constrained('instituciones')
                ->nullOnDelete();

            $table->string('logo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();

            // Configuración específica de la institución (JSON)
            $table->json('configuracion')->nullable();

            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};
