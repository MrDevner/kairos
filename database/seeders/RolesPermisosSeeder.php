<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosSeeder extends Seeder
{
    /**
     * Roles globales del sistema (capa Spatie).
     * Aplican a nivel de toda la aplicación, independientemente de institución.
     */
    private const ROLES_GLOBALES = [
        'Administrador General', // Acceso total al sistema
        'Auditor General',       // Lectura global sin restricción institucional
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ROLES_GLOBALES as $nombre) {
            Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
    }
}
