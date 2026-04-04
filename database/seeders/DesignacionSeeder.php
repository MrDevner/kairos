<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DesignacionSeeder extends Seeder
{
    public function run(): void
    {
        $fing = Institucion::where('sigla', 'FING')->first();
        $eco  = Institucion::where('sigla', 'ECO')->first();
        $rect = Institucion::where('nombre', 'like', '%Rectorado%')->first();

        if (! $fing || ! $eco || ! $rect) {
            return;
        }

        // Dependencias de FING
        $dinf  = Dependencia::where('sigla', 'DINF')->first();
        $dele  = Dependencia::where('sigla', 'DELE')->first();
        $sacad = Dependencia::where('sigla', 'SACAD')->first();

        // Dependencias de ECO
        $dce  = Dependencia::where('sigla', 'DCE')->first();
        $sadm = Dependencia::where('sigla', 'SADM')->first();

        // Dependencias de Rectorado
        $drrh   = Dependencia::where('sigla', 'DRRH')->first();
        $secgen = Dependencia::where('sigla', 'SECGEN')->first();

        // Helper: busca cargo sólo por nombre (ahora son globales)
        $cargo = fn (string $nombre) => Cargo::where('nombre', $nombre)->first();

        // [documento, cargo_nombre, institución, dependencia]
        $asignaciones = [
            ['30111222', 'Profesor/a 12 hs Nivel Superior',       $fing, $dinf],
            ['28333444', 'Profesor/a 15 hs Nivel Medio',          $fing, $dinf],
            ['32555666', 'JTP Nivel Medio',                       $fing, $dele],
            ['27777888', 'No Docente',                            $fing, $sacad],
            ['33999000', 'Jefe/Director/Coord. Depto',            $eco,  $dce],
            ['26100200', 'Profesor/a 12 hs Nivel Superior',       $eco,  $dce],
            ['34300400', 'No Docente',                            $eco,  $sadm],
            ['29500600', 'No Docente',                            $rect, $drrh],
            ['31700800', 'Secretario/a',                          $rect, $secgen],
            ['35900001', 'Rector/a / Director/a',                 $rect, $secgen],
            ['28001002', 'Profesor/a 15 hs Nivel Medio',          $fing, $dinf],
            ['30003004', 'Ayud. Clases Prácticas Nivel Superior', $fing, $dinf],
            ['33005006', 'No Docente',                            $fing, $sacad],
            ['27007008', 'Jefe/Director/Coord. Depto',            $eco,  $dce],
            ['32009010', 'No Docente',                            $eco,  $sadm],
            ['29011012', 'No Docente',                            $rect, $drrh],
            ['31013014', 'No Docente',                            $rect, $secgen],
            ['28015016', 'JTP Nivel Superior',                    $fing, $dele],
            ['34017018', 'JTP Nivel Medio',                       $fing, $dele],
            ['30019020', 'Ayud. Clases Prácticas Nivel Superior', $eco,  $dce],
        ];

        foreach ($asignaciones as [$doc, $nombreCargo, $inst, $dep]) {
            $usuario     = Usuario::where('documento', $doc)->first();
            $cargoModel  = $cargo($nombreCargo);

            if (! $usuario || ! $cargoModel || ! $dep) {
                continue;
            }

            Designacion::firstOrCreate(
                [
                    'id_usuario'     => $usuario->id,
                    'id_cargo'       => $cargoModel->id,
                    'id_institucion' => $inst->id,
                ],
                [
                    'id_dependencia'            => $dep->id,
                    'fecha_inicio'              => '2026-01-01',
                    'fecha_fin'                 => null,
                    'resolucion'                => 'RES-' . rand(100, 999) . '-2026',
                    'horas_semanales_efectivas' => null,
                    'activa'                    => true,
                ]
            );
        }
    }
}
