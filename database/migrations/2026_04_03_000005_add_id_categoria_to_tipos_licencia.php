<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_licencia', function (Blueprint $table) {
            $table->foreignId('id_categoria_cargo')
                ->nullable()
                ->after('id_institucion')
                ->constrained('categorias_cargo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_licencia', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\CategoriaCargo::class, 'id_categoria_cargo');
            $table->dropColumn('id_categoria_cargo');
        });
    }
};
