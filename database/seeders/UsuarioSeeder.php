<?php

namespace Database\Seeders;

use App\Models\RolInstitucion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['documento' => '28479741'],
            [
                'apellidos' => 'Domínguez',
                'nombres'   => 'Pablo Iván',
                'email'     => 'ivandominguez@gmail.com',
                'password'  => Hash::make('Aphab081+'),
                'activo'    => true,
            ]
        );

        $rolAdminGeneral = RolInstitucion::where('nombre', 'Administrador General')->first();

        if ($rolAdminGeneral) {
            $admin->rolesInstitucion()->firstOrCreate(
                ['id_rol_institucion' => $rolAdminGeneral->id, 'id_institucion' => null],
                ['activo' => true, 'fecha_desde' => now()->toDateString()]
            );
        }
    }
}
