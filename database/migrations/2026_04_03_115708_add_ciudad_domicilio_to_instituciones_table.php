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
        Schema::table('instituciones', function (Blueprint $table) {
            $table->foreignId('id_ciudad_domicilio')
                  ->nullable()->after('direccion')
                  ->constrained('ciudades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropForeign(['id_ciudad_domicilio']);
            $table->dropColumn('id_ciudad_domicilio');
        });
    }
};
