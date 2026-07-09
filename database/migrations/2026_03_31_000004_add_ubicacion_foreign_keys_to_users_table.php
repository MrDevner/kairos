<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega las FK de ubicación a 'users' una vez que 'ciudades', 'estados'
     * y 'paises' ya existen (las columnas se crean en
     * 0001_01_01_000000_create_users_table.php).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_ciudad_domicilio')->references('id')->on('ciudades')->nullOnDelete();
            $table->foreign('id_estado_nacimiento')->references('id')->on('estados')->nullOnDelete();
            $table->foreign('id_pais_nacimiento')->references('id')->on('paises')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_ciudad_domicilio']);
            $table->dropForeign(['id_estado_nacimiento']);
            $table->dropForeign(['id_pais_nacimiento']);
        });
    }
};
