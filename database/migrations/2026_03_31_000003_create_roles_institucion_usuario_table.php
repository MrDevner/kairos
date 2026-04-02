<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_institucion_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')
                ->constrained('usuarios')
                ->cascadeOnDelete();
            $table->foreignId('id_rol_institucion')
                ->constrained('roles_institucion')
                ->cascadeOnDelete();
            // id_institucion: FK a instituciones (tabla creada en futura migración)
            $table->unsignedBigInteger('id_institucion')->index();
            $table->boolean('activo')->default(true);
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();

            // Un usuario solo puede tener un rol activo por institución a la vez
            $table->unique(
                ['id_usuario', 'id_rol_institucion', 'id_institucion'],
                'uq_rol_usuario_institucion'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_institucion_usuario');
    }
};
