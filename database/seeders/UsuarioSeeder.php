<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin genérico
        $admin = Usuario::firstOrCreate(
            ['documento' => '99999999'],
            [
                'apellidos' => 'Administrador',
                'nombres'   => 'General',
                'email'     => 'admin@kairos.unsj.edu.ar',
                'password'  => Hash::make('Admin1234!'),
                'activo'    => true,
            ]
        );
        $admin->assignRole('Administrador General');

        // Administrador general real
        $adminReal = Usuario::firstOrCreate(
            ['documento' => '28479741'],
            [
                'apellidos' => 'Domínguez',
                'nombres'   => 'Pablo Iván',
                'email'     => 'ivandominguez@gmail.com',
                'password'  => Hash::make('Admin1234!'),
                'activo'    => true,
            ]
        );
        $adminReal->assignRole('Administrador General');

        // Personal de prueba
        $personal = [
            ['apellidos' => 'González',  'nombres' => 'María Fernanda', 'doc' => '30111222', 'sexo' => 'F'],
            ['apellidos' => 'Rodríguez', 'nombres' => 'Carlos Alberto',  'doc' => '28333444', 'sexo' => 'M'],
            ['apellidos' => 'Martínez',  'nombres' => 'Ana Lucía',       'doc' => '32555666', 'sexo' => 'F'],
            ['apellidos' => 'López',     'nombres' => 'Diego Hernán',    'doc' => '27777888', 'sexo' => 'M'],
            ['apellidos' => 'García',    'nombres' => 'Laura Beatriz',   'doc' => '33999000', 'sexo' => 'F'],
            ['apellidos' => 'Pérez',     'nombres' => 'Roberto José',    'doc' => '26100200', 'sexo' => 'M'],
            ['apellidos' => 'Sánchez',   'nombres' => 'Claudia Patricia','doc' => '34300400', 'sexo' => 'F'],
            ['apellidos' => 'Romero',    'nombres' => 'Jorge Luis',      'doc' => '29500600', 'sexo' => 'M'],
            ['apellidos' => 'Torres',    'nombres' => 'Silvana Graciela','doc' => '31700800', 'sexo' => 'F'],
            ['apellidos' => 'Flores',    'nombres' => 'Marcelo Ariel',   'doc' => '35900001', 'sexo' => 'M'],
            // 10 más variados
            ['apellidos' => 'Díaz',      'nombres' => 'Verónica Esther', 'doc' => '28001002', 'sexo' => 'F'],
            ['apellidos' => 'Moreno',    'nombres' => 'Fabián Osvaldo',  'doc' => '30003004', 'sexo' => 'M'],
            ['apellidos' => 'Jiménez',   'nombres' => 'Natalia Inés',    'doc' => '33005006', 'sexo' => 'F'],
            ['apellidos' => 'Hernández', 'nombres' => 'Pablo Daniel',    'doc' => '27007008', 'sexo' => 'M'],
            ['apellidos' => 'Álvarez',   'nombres' => 'Mónica Cecilia',  'doc' => '32009010', 'sexo' => 'F'],
            ['apellidos' => 'Vega',      'nombres' => 'Gustavo Raúl',    'doc' => '29011012', 'sexo' => 'M'],
            ['apellidos' => 'Castro',    'nombres' => 'Patricia Alicia', 'doc' => '31013014', 'sexo' => 'F'],
            ['apellidos' => 'Vargas',    'nombres' => 'Alejandro Néstor','doc' => '28015016', 'sexo' => 'M'],
            ['apellidos' => 'Ortega',    'nombres' => 'Daniela Lorena',  'doc' => '34017018', 'sexo' => 'F'],
            ['apellidos' => 'Reyes',     'nombres' => 'Sebastián Omar',  'doc' => '30019020', 'sexo' => 'M'],
        ];

        foreach ($personal as $p) {
            Usuario::firstOrCreate(
                ['documento' => $p['doc']],
                [
                    'apellidos' => $p['apellidos'],
                    'nombres'   => $p['nombres'],
                    'sexo'      => $p['sexo'],
                    'email'     => strtolower($p['doc']) . '@kairos.unsj.edu.ar',
                    'password'  => Hash::make('Kairos2026!'),
                    'activo'    => true,
                ]
            );
        }
    }
}
