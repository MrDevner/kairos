<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            // String libre validado en la app contra TicketCategoria::categorias() (no FK: la
            // categoría puede renombrarse/borrarse sin romper tickets ya creados).
            $table->string('categoria');

            $table->enum('estado', ['abierto', 'en_proceso', 'resuelto', 'cerrado'])->default('abierto');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');

            $table->foreignId('id_creador')->constrained('usuarios')->cascadeOnDelete();
            // Quién lo abrió físicamente (puede diferir del creador si soporte lo abre "en nombre de").
            $table->foreignId('id_abierto_por')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('id_asignado_a')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->date('fecha_limite')->nullable();
            $table->dateTime('fecha_cierre')->nullable();

            $table->text('categoria_cambio_motivo')->nullable();
            $table->foreignId('id_categoria_cambiada_por')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->timestamps();

            $table->index('estado');
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
