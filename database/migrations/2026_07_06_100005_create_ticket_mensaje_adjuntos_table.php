<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_mensaje_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ticket_mensaje')->constrained('ticket_mensajes')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('tipo_mime')->nullable();
            $table->unsignedBigInteger('tamanio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_mensaje_adjuntos');
    }
};
