<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Services\InformeService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerarInformeDiario extends Command
{
    protected $signature = 'kairos:informe-diario
                            {--fecha= : Fecha a procesar (Y-m-d). Por defecto: hoy.}
                            {--institucion= : ID de institución específica. Por defecto: todas.}';

    protected $description = 'Genera el informe diario de novedades para todas las instituciones activas.';

    public function handle(InformeService $informeService): int
    {
        $fecha = $this->option('fecha')
            ? Carbon::parse($this->option('fecha'))
            : Carbon::today();

        $this->info("Generando informes para: {$fecha->toDateString()}");

        $query = Institucion::activas();

        if ($idInstitucion = $this->option('institucion')) {
            $query->where('id', (int) $idInstitucion);
        }

        $instituciones = $query->get();

        if ($instituciones->isEmpty()) {
            $this->warn('No se encontraron instituciones activas.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($instituciones->count());
        $bar->start();

        $errores = [];

        foreach ($instituciones as $institucion) {
            try {
                $informe = $informeService->generarInformeDiario($institucion, $fecha);
                $urgentes = $informe->items()->where('requiere_atencion', true)->count();

                if ($urgentes > 0) {
                    $errores[] = "  [{$institucion->nombre}] {$urgentes} item(s) requieren atención urgente.";
                }
            } catch (\Throwable $e) {
                $errores[] = "  [{$institucion->nombre}] Error: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Informes generados correctamente.');

        if (!empty($errores)) {
            $this->newLine();
            $this->warn('Atención requerida:');
            foreach ($errores as $err) {
                $this->line($err);
            }
        }

        return self::SUCCESS;
    }
}
