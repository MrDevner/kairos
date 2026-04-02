<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bancos_horas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->nullable()
                ->constrained('designaciones')
                ->cascadeOnDelete();

            $table->integer('saldo_minutos')->default(0);
            $table->boolean('autorizado_acumular')->default(false);
            $table->boolean('autorizado_negativo')->default(false);

            $table->timestamps();

            $table->unique(['id_usuario', 'id_designacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bancos_horas');
    }
};
