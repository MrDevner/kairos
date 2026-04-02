<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computadores_autorizados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_dispositivo')
                ->constrained('dispositivos')
                ->cascadeOnDelete();

            $table->string('fingerprint')->unique();
            $table->string('nombre_equipo');

            $table->foreignId('id_dependencia')
                ->nullable()
                ->constrained('dependencias')
                ->nullOnDelete();

            $table->boolean('autorizado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computadores_autorizados');
    }
};
