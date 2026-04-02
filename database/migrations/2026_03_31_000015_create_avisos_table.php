<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->constrained('designaciones')
                ->cascadeOnDelete();

            $table->foreignId('id_institucion')
                ->constrained('instituciones')
                ->cascadeOnDelete();

            $table->enum('tipo', ['ausencia', 'tardanza']);
            $table->date('fecha');
            $table->time('hora_estimada_llegada')->nullable();
            $table->text('motivo')->nullable();

            $table->foreignId('id_registrado_por')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }
};
