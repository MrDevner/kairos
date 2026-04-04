<?php

namespace App\Providers;

use App\Services\BancoHorasService;
use App\Services\CalendarioService;
use App\Services\DDJJService;
use App\Services\InformeService;
use App\Services\LicenciaService;
use App\Services\MarcaService;
use App\Models\RolInstitucionUsuario;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CalendarioService::class);
        $this->app->singleton(DDJJService::class);
        $this->app->singleton(LicenciaService::class);
        $this->app->singleton(MarcaService::class);
        $this->app->singleton(BancoHorasService::class);
        $this->app->singleton(InformeService::class);
    }

    public function boot(): void
    {
        Route::bind('asignacion', fn ($value) => RolInstitucionUsuario::findOrFail($value));
    }
}
