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
            'POLI' => [
                ['nombre' => 'Administración',      'sigla' => 'ADM'],
                ['nombre' => 'Deportes',             'sigla' => 'DEP'],
                ['nombre' => 'Mantenimiento',        'sigla' => 'MANT'],
            ],
        ];

        foreach ($mapa as $sigla => $deps) {
            $inst = Institucion::where('sigla', $sigla)->first();
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
