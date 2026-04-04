<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles_institucion', function (Blueprint $table) {
            // Nivel jerárquico: número más bajo = mayor autoridad.
            // Un rol solo puede gestionar roles con nivel estrictamente mayor al propio.
            $table->unsignedSmallInteger('nivel')->default(100)->after('activo');
        });

        // Asignar niveles a roles existentes
        $niveles = [
            'Administrador'          => 50,
            'Director Administrativo'=> 20,
            'Jefe de Personal'       => 30,
            'Departamento Personal'  => 40,
            'Auditor'                => 60,
            'Usuario Comun'          => 100,
        ];

        foreach ($niveles as $nombre => $nivel) {
            DB::table('roles_institucion')->where('nombre', $nombre)->update(['nivel' => $nivel]);
        }

        // Insertar nuevo rol Director de Institución (nivel más alto)
        DB::table('roles_institucion')->insert([
            'nombre'      => 'Director de Institución',
            'descripcion' => 'Máxima autoridad institucional. Supervisa toda la gestión.',
            'activo'      => true,
            'nivel'       => 10,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('roles_institucion')->where('nombre', 'Director de Institución')->delete();

        Schema::table('roles_institucion', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });
    }
};
