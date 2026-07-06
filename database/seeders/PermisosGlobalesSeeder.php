<?php

namespace Database\Seeders;

use App\Models\RolInstitucion;
use Illuminate\Database\Seeder;

/** Catálogo de roles globales del motor de permisos (comodín "*"). */
class PermisosGlobalesSeeder extends Seeder
{
    private const ROLES_GLOBALES = [
        [
            'nombre'      => 'Administrador General',
            'descripcion' => 'Acceso total al sistema (comodín administrador).',
            'nivel'       => 1,
            'permiso'     => ['puede_ver' => true, 'puede_crear' => true, 'puede_editar' => true, 'puede_eliminar' => true],
        ],
        [
            'nombre'      => 'Auditor General',
            'descripcion' => 'Lectura global sin restricción institucional (comodín de solo lectura).',
            'nivel'       => 2,
            'permiso'     => ['puede_ver' => true, 'puede_crear' => false, 'puede_editar' => false, 'puede_eliminar' => false],
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES_GLOBALES as $datos) {
            $rol = RolInstitucion::firstOrCreate(
                ['nombre' => $datos['nombre']],
                ['descripcion' => $datos['descripcion'], 'activo' => true, 'nivel' => $datos['nivel']]
            );

            $rol->establecerPermisos('*', $datos['permiso']);
        }
    }
}
