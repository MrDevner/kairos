<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Usuario::firstOrCreate(
            ['documento' => '28479741'],
            [
                'apellidos' => 'Domínguez',
                'nombres'   => 'Pablo Iván',
                'email'     => 'ivandominguez@gmail.com',
                'password'  => Hash::make('Aphab081+'),
                'activo'    => true,
            ]
        );
        $admin->assignRole('Administrador General');
    }
}
