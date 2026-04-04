<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('id_ciudad_domicilio')
                  ->nullable()->after('domicilio')
                  ->constrained('ciudades')->nullOnDelete();

            $table->foreignId('id_estado_nacimiento')
                  ->nullable()->after('id_ciudad_domicilio')
                  ->constrained('estados')->nullOnDelete();

            $table->foreignId('id_pais_nacimiento')
                  ->nullable()->after('id_estado_nacimiento')
                  ->constrained('paises')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['id_ciudad_domicilio']);
            $table->dropForeign(['id_estado_nacimiento']);
            $table->dropForeign(['id_pais_nacimiento']);
            $table->dropColumn(['id_ciudad_domicilio', 'id_estado_nacimiento', 'id_pais_nacimiento']);
        });
    }
};
