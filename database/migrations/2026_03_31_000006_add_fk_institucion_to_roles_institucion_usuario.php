<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles_institucion_usuario', function (Blueprint $table) {
            $table->foreign('id_institucion')
                ->references('id')
                ->on('instituciones')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('roles_institucion_usuario', function (Blueprint $table) {
            $table->dropForeign(['id_institucion']);
        });
    }
};
