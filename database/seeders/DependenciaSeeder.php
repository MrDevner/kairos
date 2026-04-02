<?php

namespace Database\Seeders;

use App\Models\Dependencia;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class DependenciaSeeder extends Seeder
{
    public function run(): void
    {
        $mapa = [
            'FING' => [
                ['nombre' => 'Departamento de Informática',       'sigla' => 'DINF'],
                ['nombre' => 'Departamento de Electrónica',        'sigla' => 'DELE'],
                ['nombre' => 'Departamento de Civil',              'sigla' => 'DCIV'],
                ['nombre' => 'Secretaría Académica',               'sigla' => 'SACAD'],
                ['nombre' => 'Secretaría de Posgrado',             'sigla' => 'SPOSGRAD'],
            ],
            'ECO'  => [
                ['nombre' => 'Departamento de Ciencias Económicas','sigla' => 'DCE'],
                ['nombre' => 'Departamento de Administración',     'sigla' => 'DADM'],
                ['nombre' => 'Secretaría Administrativa',          'sigla' => 'SADM'],
            ],
            'EIND' => [
                ['nombre' => 'Departamento de Minería',            'sigla' => 'DMIN'],
                ['nombre' => 'Departamento de Geología',           'sigla' => 'DGEO'],
                ['nombre' => 'Secretaría Técnica',                 'sigla' => 'STEC'],
            ],
            'Rectorado' => [
                ['nombre' => 'Dirección de Recursos Humanos',      'sigla' => 'DRRH'],
                ['nombre' => 'Dirección de Tecnología',            'sigla' => 'DTI'],
                ['nombre' => 'Dirección de Bienestar Estudiantil', 'sigla' => 'DBE'],
                ['nombre' => 'Secretaría General',                 'sigla' => 'SECGEN'],
            ],
        ];

        foreach ($mapa as $sigla => $deps) {
            $inst = Institucion::where('sigla', $sigla)->orWhere('nombre', 'like', "%{$sigla}%")->first();
            if (!$inst) continue;

            foreach ($deps as $d) {
                Dependencia::firstOrCreate(
                    ['sigla' => $d['sigla'], 'id_institucion' => $inst->id],
                    array_merge($d, ['id_institucion' => $inst->id, 'activa' => true])
                );
            }
        }
    }
}
