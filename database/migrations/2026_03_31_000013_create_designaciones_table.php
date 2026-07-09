<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('id_cargo')
                ->constrained('cargos')
                ->cascadeOnDelete();

            $table->foreignId('id_institucion')
                ->constrained('instituciones')
                ->cascadeOnDelete();

            $table->foreignId('id_dependencia')
                ->constrained('dependencias')
                ->cascadeOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('resolucion')->nullable();

            // Horas efectivas: si es null se usan las del cargo
            $table->decimal('horas_semanales_efectivas', 5, 2)->nullable();

            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designaciones');
    }
};
