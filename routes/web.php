<?php

use App\Http\Controllers\Auth\AutenticacionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\MarcaWebController;
use App\Http\Controllers\BancoHorasController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\DDJJController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\DesignacionController;
use App\Http\Controllers\DispositivoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\LicenciaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// Página de inicio
Route::get('/', fn () => view('welcome'));

// ── Autenticación ──────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AutenticacionController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AutenticacionController::class, 'login']);

    // Recuperar contraseña
    Route::get('/contrasena/recuperar',  [ForgotPasswordController::class, 'mostrarFormulario'])->name('password.request');
    Route::post('/contrasena/recuperar', [ForgotPasswordController::class, 'enviarEnlace'])->name('password.email');
    Route::get('/contrasena/restablecer/{token}', [ResetPasswordController::class, 'mostrarFormulario'])->name('password.reset');
    Route::post('/contrasena/restablecer',        [ResetPasswordController::class, 'restablecer'])->name('password.update');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::match(['get', 'post'], '/logout', [AutenticacionController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Área protegida ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Instituciones
    Route::resource('instituciones', InstitucionController::class)
        ->parameters(['instituciones' => 'institucion']);

    // Dependencias
    Route::resource('dependencias', DependenciaController::class)
        ->parameters(['dependencias' => 'dependencia']);
    Route::post('dependencias/{dependencia}/jefe', [DependenciaController::class, 'asignarJefe'])
        ->name('dependencias.jefe');

    // Usuarios
    Route::resource('usuarios', UsuarioController::class);

    // Designaciones
    Route::resource('designaciones', DesignacionController::class);

    // DDJJ
    Route::resource('ddjj', DDJJController::class);
    Route::post('ddjj/{ddjj}/presentar', [DDJJController::class, 'presentar'])->name('ddjj.presentar');
    Route::post('ddjj/{ddjj}/aprobar',   [DDJJController::class, 'aprobar'])->name('ddjj.aprobar');
    Route::post('ddjj/{ddjj}/rechazar',  [DDJJController::class, 'rechazar'])->name('ddjj.rechazar');

    // Calendario
    Route::resource('calendario', CalendarioController::class);

    // Avisos
    Route::resource('avisos', AvisoController::class);

    // Licencias
    Route::resource('licencias', LicenciaController::class);
    Route::post('licencias/{licencia}/aprobar',  [LicenciaController::class, 'aprobar'])->name('licencias.aprobar');
    Route::post('licencias/{licencia}/rechazar', [LicenciaController::class, 'rechazar'])->name('licencias.rechazar');

    // Marcas
    Route::get('marcas',                  [MarcaController::class, 'index'])->name('marcas.index');
    Route::get('marcas/importar',         [MarcaController::class, 'importar'])->name('marcas.importar');
    Route::post('marcas/importar',        [MarcaController::class, 'procesarImportacion'])->name('marcas.importar.post');
    Route::post('marcas/procesar',        [MarcaController::class, 'procesar'])->name('marcas.procesar');
    Route::get('marcas/{usuario}/{fecha}',[MarcaController::class, 'show'])->name('marcas.show');

    // Informes
    Route::get('informes',          [InformeController::class, 'index'])->name('informes.index');
    Route::get('informes/generar',  [InformeController::class, 'generar'])->name('informes.generar');
    Route::get('informes/{informe}',[InformeController::class, 'show'])->name('informes.show');
    Route::get('informes/{informe}/excel', [InformeController::class, 'exportarExcel'])->name('informes.excel');
    Route::get('informes/{informe}/pdf',   [InformeController::class, 'exportarPdf'])->name('informes.pdf');

    // Banco de horas
    Route::get('banco-horas',          [BancoHorasController::class, 'index'])->name('banco-horas.index');
    Route::get('banco-horas/{usuario}',[BancoHorasController::class, 'show'])->name('banco-horas.show');
    Route::post('banco-horas/ajuste',  [BancoHorasController::class, 'ajuste'])->name('banco-horas.ajuste');

    // Dispositivos
    Route::resource('dispositivos', DispositivoController::class);
});

// ── Terminal de marca web (sin auth, acceso por red local) ─────────────────
Route::get('/terminal',           [MarcaWebController::class, 'terminal'])->name('marca-web.terminal');
Route::post('/terminal/marcar',   [MarcaWebController::class, 'marcar'])->name('marca-web.marcar');
Route::post('/terminal/solicitar',[MarcaWebController::class, 'solicitarAutorizacion'])->name('marca-web.solicitar');
