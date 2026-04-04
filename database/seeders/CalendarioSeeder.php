<?php

namespace Database\Seeders;

use App\Models\EventoCalendario;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class CalendarioSeeder extends Seeder
{
    public function run(): void
    {
        $unsj = Institucion::where('sigla', 'UNSJ')->first();

        // ── Feriados nacionales 2026 (generales, sin institución) ─────────────
        $feriados2026 = [
            ['fecha_inicio' => '2026-01-01', 'titulo' => 'Año Nuevo'],
            ['fecha_inicio' => '2026-02-16', 'titulo' => 'Carnaval'],
            ['fecha_inicio' => '2026-02-17', 'titulo' => 'Carnaval'],
            ['fecha_inicio' => '2026-03-24', 'titulo' => 'Día Nacional de la Memoria por la Verdad y la Justicia'],
            ['fecha_inicio' => '2026-04-02', 'titulo' => 'Día del Veterano y de los Caídos en la Guerra de Malvinas'],
            ['fecha_inicio' => '2026-04-03', 'titulo' => 'Viernes Santo'],
            ['fecha_inicio' => '2026-05-01', 'titulo' => 'Día del Trabajador'],
            ['fecha_inicio' => '2026-05-25', 'titulo' => 'Día de la Revolución de Mayo'],
            ['fecha_inicio' => '2026-06-15', 'titulo' => 'Paso a la Inmortalidad del General Martín Miguel de Güemes'],
            ['fecha_inicio' => '2026-06-20', 'titulo' => 'Paso a la Inmortalidad del General Manuel Belgrano'],
            ['fecha_inicio' => '2026-07-09', 'titulo' => 'Día de la Independencia'],
            ['fecha_inicio' => '2026-08-17', 'titulo' => 'Paso a la Inmortalidad del General José de San Martín'],
            ['fecha_inicio' => '2026-10-12', 'titulo' => 'Día del Respeto a la Diversidad Cultural'],
            ['fecha_inicio' => '2026-11-23', 'titulo' => 'Día de la Soberanía Nacional'],
            ['fecha_inicio' => '2026-12-08', 'titulo' => 'Inmaculada Concepción de María'],
            ['fecha_inicio' => '2026-12-25', 'titulo' => 'Navidad'],
        ];

        foreach ($feriados2026 as $f) {
            EventoCalendario::firstOrCreate(
                [
                    'id_institucion' => null,
                    'fecha_inicio'   => $f['fecha_inicio'],
                    'titulo'         => $f['titulo'],
                ],
                [
                    'tipo'           => 'feriado',
                    'afecta_computo' => true,
                ]
            );
        }

        // ── Receso de invierno UNSJ 2026 (evento multi-día institucional) ─────
        if ($unsj) {
            EventoCalendario::firstOrCreate(
                [
                    'id_institucion' => $unsj->id,
                    'fecha_inicio'   => '2026-07-13',
                    'titulo'         => 'Receso de Invierno',
                ],
                [
                    'fecha_fin'      => '2026-07-17',
                    'tipo'           => 'dia_no_laborable',
                    'afecta_computo' => true,
                ]
            );
        }
    }
}
