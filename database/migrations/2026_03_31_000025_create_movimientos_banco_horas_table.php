<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_banco_horas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_banco_horas')
                ->constrained('bancos_horas')
                ->cascadeOnDelete();

            $table->date('fecha');
            $table->enum('tipo', ['extra', 'faltante', 'ajuste_manual', 'autorizacion']);
            $table->integer('minutos'); // positivo o negativo

            $table->text('motivo')->nullable();

            $table->foreignId('id_marca_computada')
                ->nullable()
                ->constrained('marcas_computadas')
                ->nullOnDelete();

            $table->foreignId('id_registrado_por')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_banco_horas');
    }
};
