<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_solicitudes_resolucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ticket')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->boolean('es_solicitante')->default(false);
            $table->dateTime('aprobado_en')->nullable();
            $table->enum('estado_propuesto', ['resuelto', 'cerrado'])->nullable();
            $table->timestamps();

            $table->unique(['id_ticket', 'id_usuario']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_solicitudes_resolucion');
    }
};
