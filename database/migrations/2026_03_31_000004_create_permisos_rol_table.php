<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rol_institucion')
                ->constrained('roles_institucion')
                ->cascadeOnDelete();
            // Módulo/recurso del sistema (ej: 'usuarios', 'designaciones', 'licencias')
            $table->string('modulo');
            $table->boolean('puede_ver')->default(false);
            $table->boolean('puede_crear')->default(false);
            $table->boolean('puede_editar')->default(false);
            $table->boolean('puede_eliminar')->default(false);
            $table->timestamps();

            $table->unique(['id_rol_institucion', 'modulo'], 'uq_permiso_rol_modulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_rol');
    }
};
