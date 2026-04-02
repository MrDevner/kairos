<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('sigla')->nullable();
            $table->text('descripcion')->nullable();

            $table->foreignId('id_institucion')
                ->constrained('instituciones')
                ->cascadeOnDelete();

            // Auto-referencia: dependencia padre (nullable = es raíz de la institución)
            $table->foreignId('id_dependencia_padre')
                ->nullable()
                ->constrained('dependencias')
                ->nullOnDelete();

            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependencias');
    }
};
