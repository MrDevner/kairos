<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('errores_servidor', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_correlacion')->unique();

            $table->string('endpoint', 500)->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direccion_ip', 45)->nullable();
            $table->text('agente_usuario')->nullable();
            $table->json('parametros_solicitud')->nullable();

            $table->text('mensaje_error')->nullable();
            $table->string('clase_error')->nullable();
            $table->longText('traza_pila')->nullable();
            $table->string('archivo', 500)->nullable();
            $table->integer('linea')->nullable();

            $table->char('huella_error', 64);
            $table->unsignedInteger('cantidad_ocurrencias')->default(1);

            $table->enum('estado', ['abierto', 'en_revision', 'mitigado', 'solucionado'])->default('abierto');
            $table->foreignId('id_asignado_a')->nullable()->constrained('users')->nullOnDelete();
            $table->json('notas')->nullable();

            $table->timestamp('ultima_ocurrencia_en')->nullable();
            $table->timestamp('resuelto_en')->nullable();

            $table->timestamps();

            $table->index('huella_error');
            $table->index('estado');
            $table->index('created_at');
            $table->index('ultima_ocurrencia_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('errores_servidor');
    }
};
