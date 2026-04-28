<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Seeder;

class InstitucionesSeeder extends Seeder
{
    public function run(): void
    {
        // Raíz
        $cph = Institucion::firstOrCreate(
            ['sigla' => 'CPH'],
            [
                'nombre'               => 'El Colegio Pérez Hernández',
                'descripcion'          => 'Institución educativa El Colegio Pérez Hernández.',
                'id_institucion_padre'  => null,
                'activa'               => true,
                'configuracion'        => [
                    'umbral_jornada_minima'   => 60,
                    'banco_horas_por'         => 'usuario',
                    'permite_avisos_usuario'  => false,
                    'horas_extra_autorizadas' => false,
                ],
            ]
        );

        // Subinstitución
        Institucion::firstOrCreate(
            ['sigla' => 'POLI'],
            [
                'nombre'               => 'Polideportivo',
                'id_institucion_padre'  => $cph->id,
                'activa'               => true,
            ]
        );
    }
}
