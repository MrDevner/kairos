<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_ddjj', function (Blueprint $table) {
            $table->foreignId('id_edificio')->nullable()->after('id_dependencia')->constrained('edificios')->nullOnDelete();
            $table->foreignId('id_oficina')->nullable()->after('id_edificio')->constrained('oficinas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('horarios_ddjj', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_oficina');
            $table->dropConstrainedForeignId('id_edificio');
        });
    }
};
