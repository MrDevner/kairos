<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiempos_extra', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->nullable()
                ->constrained('designaciones')
                ->nullOnDelete();

            $table->date('fecha');
            $table->integer('minutos');
            $table->text('motivo');

            $table->foreignId('id_registrado_por')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiempos_extra');
    }
};
