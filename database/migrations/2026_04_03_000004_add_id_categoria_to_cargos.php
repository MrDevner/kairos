<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->foreignId('id_categoria')
                ->nullable()
                ->after('tipo')
                ->constrained('categorias_cargo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\CategoriaCargo::class, 'id_categoria');
            $table->dropColumn('id_categoria');
        });
    }
};
