<?php

namespace Database\Seeders;

use App\Models\Designacion;
use App\Models\Dispositivo;
use App\Models\Institucion;
use App\Models\MarcaOriginal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear un dispositivo web de prueba si no existe
        $unsj = Institucion::where('sigla', 'UNSJ')->first();
        if (!$unsj) return;

        $dispositivo = Dispositivo::firstOrCreate(
            ['nombre' => 'Terminal Web Demo', 'id_institucion' => $unsj->id],
            [
                'ubicacion'      => 'Sistema de demostración',
                'tipo'           => 'web',
                'modo_conexion'  => 'web',
                'ip_address'     => '127.0.0.1',
                'configuracion'  => null,
                'activo'         => true,
            ]
        );

        $designaciones = Designacion::with('usuario')->get();
        if ($designaciones->isEmpty()) return;

        $hoy    = Carbon::today();
        $inicio = $hoy->copy()->subDays(30);

        // Días de la semana hábiles
        $diasFeriados = [
            '2026-03-24', // Memoria
        ];

        for ($fecha = $inicio->copy(); $fecha->lte($hoy); $fecha->addDay()) {
            // Solo lunes a viernes
            if ($fecha->isWeekend()) continue;
            if (in_array($fecha->toDateString(), $diasFeriados)) continue;

            foreach ($designaciones as $i => $des) {
                // 85% de presencia
                if (rand(1, 100) > 85) continue;

                $usuario = $des->usuario;
                if (!$usuario) continue;

                // Hora de entrada con variación ±15 min
                $minutosVariacion = rand(-15, 20);
                $entrada = $fecha->copy()->setTime(7, 0)->addMinutes($minutosVariacion);

                // Hora de salida con variación
                $horasSalida = 7; // horas de jornada
                $minutosSalida = rand(-10, 15);
                $salida = $entrada->copy()->addHours($horasSalida)->addMinutes($minutosSalida);

                // 5% sin marca de salida (para simular error)
                $sinSalida = rand(1, 100) <= 5;

                MarcaOriginal::firstOrCreate(
                    [
                        'id_usuario'    => $usuario->id,
                        'id_dispositivo' => $dispositivo->id,
                        'fecha_hora'    => $entrada->toDateTimeString(),
                    ],
                    [
                        'tipo_captura' => 'web',
                        'datos_raw'    => json_encode(['tipo' => 'entrada', 'seeder' => true]),
                        'procesada'    => false,
                    ]
                );

                if (!$sinSalida) {
                    MarcaOriginal::firstOrCreate(
                        [
                            'id_usuario'    => $usuario->id,
                            'id_dispositivo' => $dispositivo->id,
                            'fecha_hora'    => $salida->toDateTimeString(),
                        ],
                        [
                            'tipo_captura' => 'web',
                            'datos_raw'    => json_encode(['tipo' => 'salida', 'seeder' => true]),
                            'procesada'    => false,
                        ]
                    );
                }
            }
        }
    }
}
