<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Services\InformeService;
use App\Services\MarcaService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DemoReset extends Command
{
    protected $signature   = 'kairos:demo-reset
                                {--sin-marcas : Omite el procesamiento de marcas}
                                {--sin-informes : Omite la generación de informes}';

    protected $description = 'Reinicia la BD de demo: migrate:fresh + seeders + procesa marcas + genera informes';

    public function __construct(
        private readonly MarcaService   $marcaService,
        private readonly InformeService $informeService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!$this->confirm('⚠ Esto borrará TODOS los datos. ¿Continuar?', false)) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        // 1. Migrate fresh
        $this->info('▶ Ejecutando migrate:fresh...');
        $this->call('migrate:fresh', ['--force' => true]);

        // 2. Seeders
        $this->info('▶ Ejecutando seeders...');
        $this->call('db:seed', ['--force' => true]);

        // 3. Procesar marcas
        if (!$this->option('sin-marcas')) {
            $this->info('▶ Procesando marcas originales...');
            $this->procesarMarcas();
        }

        // 4. Generar informes
        if (!$this->option('sin-informes')) {
            $this->info('▶ Generando informes diarios...');
            $this->generarInformes();
        }

        $this->newLine();
        $this->components->info('Demo reset completado.');

        $this->table(
            ['Credencial', 'Valor'],
            [
                ['Admin email',    'admin@kairos.unsj.edu.ar'],
                ['Admin password', 'Admin1234!'],
                ['Staff password', 'Kairos2026!'],
            ]
        );

        return self::SUCCESS;
    }

    private function procesarMarcas(): void
    {
        $hoy    = Carbon::today();
        $inicio = $hoy->copy()->subDays(30);

        $procesadas = 0;

        for ($fecha = $inicio->copy(); $fecha->lte($hoy); $fecha->addDay()) {
            if ($fecha->isWeekend()) continue;

            try {
                $this->marcaService->procesarMarcasOriginales($fecha);
                $procesadas++;
            } catch (\Throwable $e) {
                $this->warn("  Error procesando {$fecha->toDateString()}: {$e->getMessage()}");
            }
        }

        $this->line("  → {$procesadas} días procesados.");
    }

    private function generarInformes(): void
    {
        $instituciones = Institucion::all();
        $hoy    = Carbon::today();
        $inicio = $hoy->copy()->subDays(7);
        $generados = 0;

        foreach ($instituciones as $inst) {
            for ($fecha = $inicio->copy(); $fecha->lt($hoy); $fecha->addDay()) {
                if ($fecha->isWeekend()) continue;

                try {
                    $this->informeService->generarInformeDiario($inst, $fecha->toDateString());
                    $generados++;
                } catch (\Throwable $e) {
                    $this->warn("  Error informe {$inst->sigla} {$fecha->toDateString()}: {$e->getMessage()}");
                }
            }
        }

        $this->line("  → {$generados} informes generados.");
    }
}
