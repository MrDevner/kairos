<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Seeder;

class InstitucionesSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Estructura jerárquica de ejemplo: Universidad Nacional de San Juan
         *
         * UNSJ  (raíz)
         *   ├── Rectorado
         *   │     └── CREACOM
         *   ├── Colegio Central Universitario
         *   ├── Escuela de Comercio
         *   ├── Escuela Industrial
         *   └── Facultad de Ingeniería
         */

        // Raíz
        $unsj = Institucion::firstOrCreate(
            ['sigla' => 'UNSJ'],
            [
                'nombre'              => 'Universidad Nacional de San Juan',
                'descripcion'         => 'Casa de altos estudios de la provincia de San Juan.',
                'id_institucion_padre' => null,
                'activa'              => true,
                'configuracion'       => [
                    'umbral_jornada_minima'   => 60,
                    'banco_horas_por'         => 'usuario',
                    'permite_avisos_usuario'  => false,
                    'horas_extra_autorizadas' => false,
                ],
            ]
        );

        // Nivel 1 — hijas directas de UNSJ
        $rectorado = Institucion::firstOrCreate(
            ['sigla' => 'RECT'],
            [
                'nombre'               => 'Rectorado',
                'id_institucion_padre'  => $unsj->id,
                'activa'               => true,
            ]
        );

        Institucion::firstOrCreate(
            ['sigla' => 'CCU'],
            [
                'nombre'               => 'Colegio Central Universitario',
                'id_institucion_padre'  => $unsj->id,
                'activa'               => true,
            ]
        );

        Institucion::firstOrCreate(
            ['sigla' => 'ECO'],
            [
                'nombre'               => 'Escuela de Comercio',
                'id_institucion_padre'  => $unsj->id,
                'activa'               => true,
            ]
        );

        Institucion::firstOrCreate(
            ['sigla' => 'EIND'],
            [
                'nombre'               => 'Escuela Industrial',
                'id_institucion_padre'  => $unsj->id,
                'activa'               => true,
            ]
        );

        Institucion::firstOrCreate(
            ['sigla' => 'FING'],
            [
                'nombre'               => 'Facultad de Ingeniería',
                'id_institucion_padre'  => $unsj->id,
                'activa'               => true,
            ]
        );

        // Nivel 2 — hija del Rectorado
        Institucion::firstOrCreate(
            ['sigla' => 'CREACOM'],
            [
                'nombre'               => 'CREACOM',
                'descripcion'          => 'Centro de Recursos Educativos y Acción Comunitaria.',
                'id_institucion_padre'  => $rectorado->id,
                'activa'               => true,
            ]
        );
    }
}
