<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declaraciones_juradas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_usuario')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('id_designacion')
                ->constrained('designaciones')
                ->cascadeOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->enum('estado', ['borrador', 'presentada', 'aprobada', 'rechazada'])
                ->default('borrador');

            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaraciones_juradas');
    }
};
