<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->foreignId('id_tipo_licencia')
                ->nullable()
                ->after('motivo')
                ->constrained('tipos_licencia')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->dropForeign(['id_tipo_licencia']);
            $table->dropColumn('id_tipo_licencia');
        });
    }
};
