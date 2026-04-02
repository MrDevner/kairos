<?php

namespace Database\Seeders;

use App\Models\DeclaracionJurada;
use App\Models\Designacion;
use App\Models\HorarioDdjj;
use Illuminate\Database\Seeder;

class DDJJSeeder extends Seeder
{
    public function run(): void
    {
        // Horarios base por tipo de cargo (horas semanales)
        $horarioDocente = [
            ['dia' => 'lunes',     'entrada' => '08:00', 'salida' => '12:00', 'modalidad' => 'presencial'],
            ['dia' => 'martes',    'entrada' => '08:00', 'salida' => '12:00', 'modalidad' => 'presencial'],
            ['dia' => 'miercoles', 'entrada' => '08:00', 'salida' => '12:00', 'modalidad' => 'presencial'],
            ['dia' => 'jueves',    'entrada' => '08:00', 'salida' => '12:00', 'modalidad' => 'presencial'],
            ['dia' => 'viernes',   'entrada' => '08:00', 'salida' => '12:00', 'modalidad' => 'presencial'],
        ];

        $horarioAdministrativo = [
            ['dia' => 'lunes',     'entrada' => '07:00', 'salida' => '14:00', 'modalidad' => 'presencial'],
            ['dia' => 'martes',    'entrada' => '07:00', 'salida' => '14:00', 'modalidad' => 'presencial'],
            ['dia' => 'miercoles', 'entrada' => '07:00', 'salida' => '14:00', 'modalidad' => 'presencial'],
            ['dia' => 'jueves',    'entrada' => '07:00', 'salida' => '14:00', 'modalidad' => 'presencial'],
            ['dia' => 'viernes',   'entrada' => '07:00', 'salida' => '14:00', 'modalidad' => 'presencial'],
        ];

        $horarioMixto = [
            ['dia' => 'lunes',     'entrada' => '08:00', 'salida' => '13:00', 'modalidad' => 'presencial'],
            ['dia' => 'martes',    'entrada' => '08:00', 'salida' => '13:00', 'modalidad' => 'remoto'],
            ['dia' => 'miercoles', 'entrada' => '08:00', 'salida' => '13:00', 'modalidad' => 'presencial'],
            ['dia' => 'jueves',    'entrada' => '08:00', 'salida' => '13:00', 'modalidad' => 'remoto'],
            ['dia' => 'viernes',   'entrada' => '08:00', 'salida' => '11:00', 'modalidad' => 'presencial'],
        ];

        $estados = ['aprobada', 'aprobada', 'aprobada', 'presentada', 'borrador'];

        $designaciones = Designacion::with(['cargo', 'usuario'])->get();

        foreach ($designaciones as $i => $des) {
            // Elegir horario según tipo de cargo
            $tipo = $des->cargo->tipo ?? 'cargo';
            $nombreCargo = $des->cargo->nombre ?? '';

            if (str_contains($nombreCargo, 'Administrativo') || str_contains($nombreCargo, 'No Docente')) {
                $horarios = $horarioAdministrativo;
            } elseif (str_contains($nombreCargo, 'Director') || str_contains($nombreCargo, 'Secretario')) {
                $horarios = $horarioAdministrativo;
            } elseif ($i % 4 === 0) {
                $horarios = $horarioMixto;
            } else {
                $horarios = $horarioDocente;
            }

            $estado = $estados[$i % count($estados)];

            $ddjj = DeclaracionJurada::firstOrCreate(
                ['id_designacion' => $des->id, 'fecha_inicio' => '2026-01-01'],
                [
                    'id_usuario'   => $des->id_usuario,
                    'fecha_fin'    => null,
                    'estado'       => $estado,
                    'observaciones' => $estado === 'rechazada' ? 'Horario incompleto, favor corregir.' : null,
                ]
            );

            // Solo crear horarios si no existen
            if ($ddjj->wasRecentlyCreated || $ddjj->horarios()->count() === 0) {
                foreach ($horarios as $h) {
                    HorarioDdjj::firstOrCreate(
                        [
                            'id_declaracion_jurada' => $ddjj->id,
                            'dia_semana'            => $h['dia'],
                        ],
                        [
                            'hora_entrada'            => $h['entrada'],
                            'hora_salida'             => $h['salida'],
                            'modalidad'               => $h['modalidad'],
                            'id_institucion_externa'  => null,
                            'id_dependencia'          => null,
                        ]
                    );
                }
            }
        }
    }
}
