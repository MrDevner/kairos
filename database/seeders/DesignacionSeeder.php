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

        if (!$fing || !$eco || !$rect) return;

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

        // Cargos
        $cargos = Cargo::all()->groupBy(fn($c) => $c->id_institucion . '_' . $c->nombre);

        $cargoFing = fn(string $nombre) => Cargo::where('nombre', $nombre)
            ->where('id_institucion', $fing->id)->first();
        $cargoEco  = fn(string $nombre) => Cargo::where('nombre', $nombre)
            ->where('id_institucion', $eco->id)->first();
        $cargoRect = fn(string $nombre) => Cargo::where('nombre', $nombre)
            ->where('id_institucion', $rect->id)->first();

        // Usuarios (documentos asignados en UsuarioSeeder)
        $usuarios = Usuario::whereNotIn('documento', ['99999999'])
            ->orderBy('id')->get();

        if ($usuarios->isEmpty()) return;

        $asignaciones = [
            // [usuario_doc, cargo_nombre, institucion, dependencia]
            ['30111222', 'Profesor Titular',        $fing, $dinf],
            ['28333444', 'Profesor Adjunto',         $fing, $dinf],
            ['32555666', 'Jefe de Trabajos Prácticos',$fing, $dele],
            ['27777888', 'Personal Administrativo',  $fing, $sacad],
            ['33999000', 'Profesor Titular',         $eco,  $dce],
            ['26100200', 'Profesor Asociado',         $eco,  $dce],
            ['34300400', 'Personal Administrativo',  $eco,  $sadm],
            ['29500600', 'Personal No Docente',      $rect, $drrh],
            ['31700800', 'Secretario',               $rect, $secgen],
            ['35900001', 'Director',                 $rect, $secgen],
            ['28001002', 'Profesor Adjunto',         $fing, $dinf],
            ['30003004', 'Ayudante de Primera',      $fing, $dinf],
            ['33005006', 'Personal No Docente',      $fing, $sacad],
            ['27007008', 'Profesor Titular',         $eco,  $dce],
            ['32009010', 'Personal Administrativo',  $eco,  $sadm],
            ['29011012', 'Personal No Docente',      $rect, $drrh],
            ['31013014', 'Personal Administrativo',  $rect, $secgen],
            ['28015016', 'Profesor Asociado',         $fing, $dele],
            ['34017018', 'Jefe de Trabajos Prácticos',$fing, $dele],
            ['30019020', 'Ayudante de Primera',      $eco,  $dce],
        ];

        foreach ($asignaciones as [$doc, $nombreCargo, $inst, $dep]) {
            $usuario = Usuario::where('documento', $doc)->first();
            $cargo   = Cargo::where('nombre', $nombreCargo)
                ->where('id_institucion', $inst->id)->first();

            if (!$usuario || !$cargo || !$dep) continue;

            Designacion::firstOrCreate(
                ['id_usuario' => $usuario->id, 'id_cargo' => $cargo->id, 'id_institucion' => $inst->id],
                [
                    'id_dependencia'             => $dep->id,
                    'fecha_inicio'               => '2026-01-01',
                    'fecha_fin'                  => null,
                    'resolucion'                 => 'RES-' . rand(100, 999) . '-2026',
                    'horas_semanales_efectivas'  => null,
                    'activa'                     => true,
                ]
            );
        }
    }
}
