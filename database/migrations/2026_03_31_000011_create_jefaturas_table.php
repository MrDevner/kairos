<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jefaturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_dependencia')
                ->constrained('dependencias')
                ->cascadeOnDelete();

            $table->foreignId('id_usuario')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('cargo', 100)->nullable();
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jefaturas');
    }
};
