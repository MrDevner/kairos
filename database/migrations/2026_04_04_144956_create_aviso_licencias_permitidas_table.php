<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aviso_licencias_permitidas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_institucion');
            $table->unsignedBigInteger('id_tipo_licencia');

            $table->primary(['id_institucion', 'id_tipo_licencia']);

            $table->foreign('id_institucion')
                  ->references('id')->on('instituciones')
                  ->cascadeOnDelete();

            $table->foreign('id_tipo_licencia')
                  ->references('id')->on('tipos_licencia')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aviso_licencias_permitidas');
    }
};
