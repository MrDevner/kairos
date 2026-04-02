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
            'nombre'      => 'Administrador',
            'descripcion' => 'Acceso completo a la gestión de la institución.',
        ],
        [
            'nombre'      => 'Director Administrativo',
            'descripcion' => 'Supervisión general de procesos administrativos.',
        ],
        [
            'nombre'      => 'Jefe de Personal',
            'descripcion' => 'Gestión del personal de la institución.',
        ],
        [
            'nombre'      => 'Departamento Personal',
            'descripcion' => 'Operaciones de gestión de personal.',
        ],
        [
            'nombre'      => 'Auditor',
            'descripcion' => 'Acceso de lectura para auditoría institucional.',
        ],
        [
            'nombre'      => 'Usuario Comun',
            'descripcion' => 'Acceso básico de consulta.',
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $datos) {
            RolInstitucion::firstOrCreate(
                ['nombre' => $datos['nombre']],
                ['descripcion' => $datos['descripcion'], 'activo' => true]
            );
        }
    }
}
