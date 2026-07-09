<?php

namespace Database\Seeders;

use App\Models\Designacion;
use App\Models\Licencia;
use App\Models\TipoLicencia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LicenciaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('documento', '99999999')->first();
        if (!$admin) return;

        // Crear tipos de licencia si no existen
        $tiposBase = [
            ['nombre' => 'Licencia por Enfermedad',     'computo' => 'dias_corridos', 'afecta' => 'designacion', 'dias_maximos' => 30,  'requiere_documentacion' => true],
            ['nombre' => 'Licencia por Maternidad',     'computo' => 'dias_corridos', 'afecta' => 'usuario',     'dias_maximos' => 90,  'requiere_documentacion' => true],
            ['nombre' => 'Licencia por Estudio',        'computo' => 'dias_habiles',  'afecta' => 'designacion', 'dias_maximos' => 10,  'requiere_documentacion' => true],
            ['nombre' => 'Licencia por Asunto Particular','computo' => 'dias_habiles','afecta' => 'designacion', 'dias_maximos' => 5,   'requiere_documentacion' => false],
            ['nombre' => 'Licencia por Fallecimiento',  'computo' => 'dias_corridos', 'afecta' => 'usuario',     'dias_maximos' => 5,   'requiere_documentacion' => false],
        ];

        foreach ($tiposBase as $t) {
            TipoLicencia::firstOrCreate(
                ['nombre' => $t['nombre']],
                array_merge($t, ['activo' => true])
            );
        }

        $tipoEnfermedad  = TipoLicencia::where('nombre', 'Licencia por Enfermedad')->first();
        $tipoEstudio     = TipoLicencia::where('nombre', 'Licencia por Estudio')->first();
        $tipoParticular  = TipoLicencia::where('nombre', 'Licencia por Asunto Particular')->first();
        $tipoMaternidad  = TipoLicencia::where('nombre', 'Licencia por Maternidad')->first();

        if (!$tipoEnfermedad) return;

        $designaciones = Designacion::with('usuario')->get();

        $licencias = [
            // [índice_des, tipo, fecha_inicio, fecha_fin, dias, estado, motivo]
            [0, $tipoEnfermedad,  '2026-02-10', '2026-02-14', 5,  'aprobada',  'Gripe estacional'],
            [1, $tipoEstudio,     '2026-03-05', '2026-03-06', 2,  'aprobada',  'Examen final'],
            [2, $tipoParticular,  '2026-03-15', '2026-03-15', 1,  'aprobada',  'Trámite personal'],
            [3, $tipoEnfermedad,  '2026-03-20', '2026-03-27', 8,  'pendiente', 'Intervención quirúrgica'],
            [4, $tipoEstudio,     '2026-02-20', '2026-02-21', 2,  'aprobada',  'Defensa de tesis'],
            [5, $tipoParticular,  '2026-03-28', '2026-03-28', 1,  'pendiente', 'Trámite bancario'],
            [6, $tipoEnfermedad,  '2026-01-15', '2026-01-17', 3,  'aprobada',  'Infección respiratoria'],
            [7, $tipoEstudio,     '2026-03-10', '2026-03-10', 1,  'rechazada', 'Sin documentación adjunta'],
            [8, $tipoParticular,  '2026-02-05', '2026-02-05', 1,  'aprobada',  'Gestión notarial'],
        ];

        foreach ($licencias as [$idx, $tipo, $fi, $ff, $dias, $estado, $motivo]) {
            if (!isset($designaciones[$idx])) continue;
            $des = $designaciones[$idx];

            Licencia::firstOrCreate(
                [
                    'id_usuario'      => $des->id_usuario,
                    'id_tipo_licencia' => $tipo->id,
                    'fecha_inicio'    => $fi,
                ],
                [
                    'id_designacion'            => $des->id,
                    'id_registrado_por'         => $admin->id,
                    'id_aprobado_por'           => in_array($estado, ['aprobada', 'rechazada']) ? $admin->id : null,
                    'fecha_fin'                 => $ff,
                    'dias_computados'           => $dias,
                    'estado'                    => $estado,
                    'motivo'                    => $motivo,
                    'documentacion'             => null,
                    'fecha_aprobacion'          => in_array($estado, ['aprobada', 'rechazada']) ? now() : null,
                    'observaciones_aprobacion'  => $estado === 'rechazada' ? 'Falta documentación respaldatoria.' : null,
                ]
            );
        }
    }
}
