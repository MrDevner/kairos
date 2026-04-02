<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generar informe diario de todas las instituciones al finalizar la jornada laboral
Schedule::command('kairos:informe-diario')->dailyAt('20:00');
