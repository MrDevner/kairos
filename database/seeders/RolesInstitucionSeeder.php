<?php

namespace Database\Seeders;

use App\Models\RolInstitucion;
use Illuminate\Database\Seeder;

class RolesInstitucionSeeder extends Seeder
{
    /**
     * Catálogo de roles por institución.
     * Estos son los tipos de rol disponibles para asignar a usuarios
     * dentro de cada institución.
     */
    private const ROLES = [
        [
            'nombre'      => 'Director de Institución',
            'descripcion' => 'Máxima autoridad institucional. Supervisa toda la gestión.',
            'nivel'       => 10,
        ],
        [
            'nombre'      => 'Director Administrativo',
            'descripcion' => 'Supervisión general de procesos administrativos.',
            'nivel'       => 20,
        ],
        [
            'nombre'      => 'Jefe de Personal',
            'descripcion' => 'Gestión del personal de la institución.',
            'nivel'       => 30,
        ],
        [
            'nombre'      => 'Departamento Personal',
            'descripcion' => 'Operaciones de gestión de personal.',
            'nivel'       => 40,
        ],
        [
            'nombre'      => 'Administrador',
            'descripcion' => 'Acceso completo a la gestión de la institución.',
            'nivel'       => 50,
        ],
        [
            'nombre'      => 'Auditor',
            'descripcion' => 'Acceso de lectura para auditoría institucional.',
            'nivel'       => 60,
        ],
        [
            'nombre'      => 'Usuario Comun',
            'descripcion' => 'Acceso básico de consulta.',
            'nivel'       => 100,
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $datos) {
            RolInstitucion::firstOrCreate(
                ['nombre' => $datos['nombre']],
                ['descripcion' => $datos['descripcion'], 'activo' => true, 'nivel' => $datos['nivel']]
            );
        }
    }
}
