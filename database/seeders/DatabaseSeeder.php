<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Roles institucionales
            RolesInstitucionSeeder::class,
            // Roles globales del motor de permisos (comodín *)
            PermisosGlobalesSeeder::class,
            // Instituciones (El Colegio Pérez Hernández + Polideportivo)
            InstitucionesSeeder::class,
            // Dependencias por institución
            DependenciaSeeder::class,
            // Cargos por institución
            CargoSeeder::class,
            // Usuarios (administrador general)
            UsuarioSeeder::class,
            // Designaciones (usuario + cargo + dependencia)
            DesignacionSeeder::class,
            // Declaraciones juradas con horarios
            DDJJSeeder::class,
            // Tipos de licencia + licencias de ejemplo
            LicenciaSeeder::class,
            // Calendario: feriados 2026
            CalendarioSeeder::class,
            // Marcas originales (últimos 30 días)
            MarcaSeeder::class,
            // Informes diarios de la última semana
            InformeSeeder::class,
        ]);
    }
}
