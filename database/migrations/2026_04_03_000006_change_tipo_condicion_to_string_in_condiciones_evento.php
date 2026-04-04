<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condiciones_evento', function (Blueprint $table) {
            // Cambiar de enum a string para permitir nuevos tipos sin migrations
            $table->string('tipo_condicion', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('condiciones_evento', function (Blueprint $table) {
            $table->enum('tipo_condicion', ['sexo', 'cargo', 'dependencia', 'custom'])->change();
        });
    }
};
