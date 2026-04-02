<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = Institucion::all()->keyBy('sigla');

        $cargos = [
            ['nombre' => 'Profesor Titular',           'horas_sem' => 20, 'tipo' => 'cargo'],
            ['nombre' => 'Profesor Asociado',          'horas_sem' => 20, 'tipo' => 'cargo'],
            ['nombre' => 'Profesor Adjunto',           'horas_sem' => 15, 'tipo' => 'cargo'],
            ['nombre' => 'Jefe de Trabajos Prácticos', 'horas_sem' => 10, 'tipo' => 'cargo'],
            ['nombre' => 'Ayudante de Primera',        'horas_sem' => 5,  'tipo' => 'cargo'],
            ['nombre' => 'Personal Administrativo',    'horas_sem' => 35, 'tipo' => 'cargo'],
            ['nombre' => 'Personal No Docente',        'horas_sem' => 35, 'tipo' => 'cargo'],
            ['nombre' => 'Director',                   'horas_sem' => 40, 'tipo' => 'cargo'],
            ['nombre' => 'Secretario',                 'horas_sem' => 40, 'tipo' => 'cargo'],
            ['nombre' => '10 hs Cátedra',              'horas_sem' => 10, 'tipo' => 'horas_catedra'],
            ['nombre' => '15 hs Cátedra',              'horas_sem' => 15, 'tipo' => 'horas_catedra'],
            ['nombre' => '20 hs Cátedra',              'horas_sem' => 20, 'tipo' => 'horas_catedra'],
        ];

        foreach (Institucion::all() as $inst) {
            foreach ($cargos as $c) {
                Cargo::firstOrCreate(
                    ['nombre' => $c['nombre'], 'id_institucion' => $inst->id],
                    [
                        'horas_semanales' => $c['horas_sem'],
                        'tipo'            => $c['tipo'],
                        'activo'          => true,
                    ]
                );
            }
        }
    }
}
