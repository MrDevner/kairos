<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('horas_semanales', 5, 2);
            $table->decimal('indice', 8, 4)->nullable()->comment('Índice salarial por hora reloj');

            $table->foreignId('id_categoria')
                ->nullable()
                ->constrained('categorias_cargo')
                ->nullOnDelete();

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
