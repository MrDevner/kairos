<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\CategoriaCargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que la categoría existe
        $categoria = CategoriaCargo::firstOrCreate(
            ['nombre' => 'Docente'],
            ['activo' => true]
        );

        $cargos = [
            ['nombre' => 'Preceptor/a',                           'horas_semanales' => 25, 'indice' => 0.4800],
            ['nombre' => 'Subjefe/a de Preceptores',              'horas_semanales' => 25, 'indice' => 0.5333],
            ['nombre' => 'Jefe/a de Preceptores',                 'horas_semanales' => 10, 'indice' => 0.5760],
            ['nombre' => 'Bibliotecario/a',                       'horas_semanales' => 25, 'indice' => 0.4800],
            ['nombre' => 'Jefe/a de Biblioteca',                  'horas_semanales' => 12, 'indice' => 0.5760],
            ['nombre' => 'Profesor/a Equipo de Orientación',      'horas_semanales' => 12, 'indice' => 0.7000],
            ['nombre' => 'Ayudante Equipo de Orientación',        'horas_semanales' => 12, 'indice' => 0.5833],
            ['nombre' => 'Rector/a / Director/a',                 'horas_semanales' => 16, 'indice' => 1.3500],
            ['nombre' => 'Vicerrector/a / Vicedirector/a',        'horas_semanales' => 16, 'indice' => 1.1921],
            ['nombre' => 'Regente',                               'horas_semanales' => 16, 'indice' => 0.8000],
            ['nombre' => 'Subregente/a',                          'horas_semanales' => 16, 'indice' => 0.6800],
            ['nombre' => 'Asesor/a Pedagógico',                  'horas_semanales' =>  8, 'indice' => 0.8395],
            ['nombre' => 'Secretario/a',                          'horas_semanales' => 12, 'indice' => 0.8000],
            ['nombre' => 'Prosecretario/a',                       'horas_semanales' => 12, 'indice' => 0.6800],
            ['nombre' => 'Maestro/a Jardín Maternal',            'horas_semanales' => 15, 'indice' => 0.7066],
            ['nombre' => 'Maestro/a Jardín de Infantes',         'horas_semanales' => 25, 'indice' => 0.6867],
            ['nombre' => 'Maestro/a Especial Nivel Inicial',      'horas_semanales' => 25, 'indice' => 0.7882],
            ['nombre' => 'Maestro/a Grado',                      'horas_semanales' => 25, 'indice' => 0.6667],
            ['nombre' => 'Maestro/a Especial Nivel Primario',     'horas_semanales' => 25, 'indice' => 0.7882],
            ['nombre' => 'Profesor/a 15 hs Nivel Medio',          'horas_semanales' => 25, 'indice' => 1.0000],
            ['nombre' => 'Maestro/a Coordinador',                 'horas_semanales' => 20, 'indice' => 0.5664],
            ['nombre' => 'Ayud. Clases Prácticas Nivel Medio',   'horas_semanales' => 12, 'indice' => 0.5320],
            ['nombre' => 'JTP Nivel Medio',                       'horas_semanales' => 25, 'indice' => 0.6384],
            ['nombre' => 'Ayudante Técnico TP',                  'horas_semanales' => 25, 'indice' => 0.5320],
            ['nombre' => 'Maestro/a Enseñanza Práctica',        'horas_semanales' => 25, 'indice' => 0.7000],
            ['nombre' => 'Maestro/a Ens. Práctica Jefe Sección', 'horas_semanales' => 25, 'indice' => 0.7700],
            ['nombre' => 'Jefe/a Gral. Enseñanza Práctica',     'horas_semanales' => 25, 'indice' => 0.8400],
            ['nombre' => 'Jefe/a Gral. Taller',                  'horas_semanales' => 25, 'indice' => 0.9100],
            ['nombre' => 'Profesor/a 12 hs Nivel Superior',       'horas_semanales' => 20, 'indice' => 1.2500],
            ['nombre' => 'Ayud. Clases Prácticas Nivel Superior', 'horas_semanales' => 20, 'indice' => 0.6650],
            ['nombre' => 'JTP Nivel Superior',                    'horas_semanales' => 20, 'indice' => 0.7980],
            ['nombre' => 'Jefe/Director/Coord. Depto',            'horas_semanales' => 20, 'indice' => 1.0500],
            ['nombre' => 'No Docente',                             'horas_semanales' => 35, 'indice' => null],
        ];

        foreach ($cargos as $data) {
            Cargo::updateOrCreate(
                ['nombre' => $data['nombre']],
                [
                    'horas_semanales' => $data['horas_semanales'],
                    'indice'          => $data['indice'],
                    'id_categoria'    => $categoria->id,
                    'activo'          => true,
                ]
            );
        }
    }
}
