<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_institucion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);

            // Nivel jerárquico: número más bajo = mayor autoridad.
            // Un rol solo puede gestionar roles con nivel estrictamente mayor al propio.
            $table->unsignedSmallInteger('nivel')->default(100);

            $table->timestamps();

            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_institucion');
    }
};
