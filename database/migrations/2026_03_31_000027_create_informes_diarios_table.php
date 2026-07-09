<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_diarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_institucion')
                ->constrained('instituciones')
                ->cascadeOnDelete();

            $table->date('fecha');
            $table->dateTime('generado_en')->nullable();

            $table->enum('estado', ['generado', 'revisado', 'cerrado'])
                ->default('generado');

            $table->foreignId('id_generado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['id_institucion', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_diarios');
    }
};
