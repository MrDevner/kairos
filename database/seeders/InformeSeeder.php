<?php

namespace Database\Seeders;

use App\Models\InformeDiario;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InformeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Usuario::where('documento', '99999999')->first();
        $unsj  = Institucion::where('sigla', 'UNSJ')->first();

        if (!$admin || !$unsj) return;

        $hoy    = Carbon::today();
        $inicio = $hoy->copy()->subDays(7);

        for ($fecha = $inicio->copy(); $fecha->lt($hoy); $fecha->addDay()) {
            if ($fecha->isWeekend()) continue;

            $estado = match (true) {
                $fecha->lt($hoy->copy()->subDays(3)) => 'cerrado',
                $fecha->lt($hoy->copy()->subDays(1)) => 'revisado',
                default                              => 'generado',
            };

            InformeDiario::firstOrCreate(
                ['id_institucion' => $unsj->id, 'fecha' => $fecha->toDateString()],
                [
                    'generado_en'    => $fecha->copy()->setTime(20, 0),
                    'estado'         => $estado,
                    'id_generado_por' => $admin->id,
                ]
            );
        }
    }
}
