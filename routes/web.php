<?php

use App\Http\Controllers\Auth\AutenticacionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AsignacionRolController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\CategoriaCargoController;
use App\Http\Controllers\MarcaWebController;
use App\Http\Controllers\BancoHorasController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\DDJJController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\DesignacionController;
use App\Http\Controllers\DispositivoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\InstitucionActivaController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\LicenciaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\RolInstitucionController;
use App\Http\Controllers\TipoLicenciaController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\UbicacionController;
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

    // Perfil del usuario autenticado
    Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::get('/perfil/google/vincular',    [GoogleController::class, 'vincular'])->name('perfil.google.vincular');
    Route::post('/perfil/google/desvincular',[GoogleController::class, 'desvincular'])->name('perfil.google.desvincular');

    // Ubicación geográfica (cascading selects)
    Route::get('/ubicacion/estados',  [UbicacionController::class, 'estados'])->name('ubicacion.estados');
    Route::get('/ubicacion/ciudades', [UbicacionController::class, 'ciudades'])->name('ubicacion.ciudades');

    // Selección de institución activa
    Route::post('/institucion-activa', [InstitucionActivaController::class, 'cambiar'])->name('institucion-activa.cambiar');

    // Instituciones
    Route::resource('instituciones', InstitucionController::class)
        ->parameters(['instituciones' => 'institucion']);
    Route::post('instituciones/{institucion}/aviso-licencias',
        [InstitucionController::class, 'guardarAvisoLicencias'])
        ->name('instituciones.aviso-licencias');
    Route::post('instituciones/{institucion}/autorizadores-licencias',
        [InstitucionController::class, 'guardarAutorizadoresLicencias'])
        ->name('instituciones.autorizadores-licencias');

    // Dependencias
    Route::resource('dependencias', DependenciaController::class)
        ->parameters(['dependencias' => 'dependencia']);
    Route::post('dependencias/{dependencia}/jefe',          [DependenciaController::class, 'asignarJefe'])->name('dependencias.jefe');
    Route::delete('dependencias/{dependencia}/jefe',        [DependenciaController::class, 'darDeBajaJefe'])->name('dependencias.jefe.baja');

    // Usuarios
    Route::get('usuarios/buscar', [UsuarioController::class, 'buscar'])->name('usuarios.buscar');
    Route::resource('usuarios', UsuarioController::class);

    // Gestión de roles de usuario
    Route::post('usuarios/{usuario}/roles',          [AsignacionRolController::class, 'store'])->name('usuarios.roles.store');
    Route::put('usuarios/roles/{asignacion}',        [AsignacionRolController::class, 'update'])->name('usuarios.roles.update');
    Route::delete('usuarios/roles/{asignacion}',     [AsignacionRolController::class, 'destroy'])->name('usuarios.roles.destroy');
    Route::post('usuarios/{usuario}/roles-global',   [AsignacionRolController::class, 'asignarGlobal'])->name('usuarios.roles.global.store');
    Route::delete('usuarios/{usuario}/roles-global', [AsignacionRolController::class, 'revocarGlobal'])->name('usuarios.roles.global.destroy');

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
    Route::get('avisos/designaciones-usuario', [AvisoController::class, 'designacionesPorUsuario'])->name('avisos.designaciones-usuario');
    Route::resource('avisos', AvisoController::class);

    // Licencias
    Route::resource('licencias', LicenciaController::class);
    Route::post('licencias/{licencia}/aprobar',  [LicenciaController::class, 'aprobar'])->name('licencias.aprobar');
    Route::post('licencias/{licencia}/rechazar', [LicenciaController::class, 'rechazar'])->name('licencias.rechazar');

    // Marcas — rutas estáticas antes del resource para evitar colisión con {marca}
    Route::get('marcas/importar',  [MarcaController::class, 'importar'])->name('marcas.importar');
    Route::post('marcas/importar', [MarcaController::class, 'procesarImportacion'])->name('marcas.importar.post');
    Route::post('marcas/procesar', [MarcaController::class, 'procesar'])->name('marcas.procesar');
    Route::resource('marcas', MarcaController::class)->parameters(['marcas' => 'marca']);

    // Informes
    Route::get('informes',                     [InformeController::class, 'index'])->name('informes.index');
    Route::post('informes',                    [InformeController::class, 'generar'])->name('informes.store');
    Route::get('informes/marcas',              [MarcaController::class, 'computadas'])->name('informes.marcas');
    Route::get('informes/resumen-dependencia', [InformeController::class, 'resumenDependencia'])->name('informes.resumen-dependencia');
    Route::get('informes/{informe}',           [InformeController::class, 'show'])->name('informes.show');
    Route::delete('informes/{informe}',        [InformeController::class, 'destroy'])->name('informes.destroy');
    Route::get('informes/{informe}/excel',     [InformeController::class, 'exportarExcel'])->name('informes.excel');
    Route::get('informes/{informe}/pdf',       [InformeController::class, 'exportarPdf'])->name('informes.pdf');

    // Banco de horas
    Route::get('banco-horas',          [BancoHorasController::class, 'index'])->name('banco-horas.index');
    Route::get('banco-horas/{usuario}',[BancoHorasController::class, 'show'])->name('banco-horas.show');
    Route::post('banco-horas/ajuste',  [BancoHorasController::class, 'ajuste'])->name('banco-horas.ajuste');

    // Dispositivos
    Route::resource('dispositivos', DispositivoController::class);
    Route::post('dispositivos/{dispositivo}/computadores/{computador}/autorizar',
        [DispositivoController::class, 'autorizarComputador'])->name('dispositivos.computadores.autorizar');
    Route::delete('dispositivos/{dispositivo}/computadores/{computador}',
        [DispositivoController::class, 'eliminarComputador'])->name('dispositivos.computadores.eliminar');

    // Roles y permisos
    Route::resource('roles', RolInstitucionController::class);

    // Tipos de licencia
    Route::resource('tipos-licencia', TipoLicenciaController::class)
        ->parameters(['tipos-licencia' => 'tipo']);

    // Cargos
    Route::resource('cargos', CargoController::class)
        ->except(['show']);

    // Categorías de cargo
    Route::get('categorias-cargo',                          [CategoriaCargoController::class, 'index'])->name('categorias-cargo.index');
    Route::post('categorias-cargo',                         [CategoriaCargoController::class, 'store'])->name('categorias-cargo.store');
    Route::put('categorias-cargo/{categoriasCargo}',        [CategoriaCargoController::class, 'update'])->name('categorias-cargo.update');
    Route::delete('categorias-cargo/{categoriasCargo}',     [CategoriaCargoController::class, 'destroy'])->name('categorias-cargo.destroy');

    // Logs de actividad
    Route::get('logs',          [LogController::class, 'index'])->name('logs.index');
    Route::get('logs/{activity}',[LogController::class, 'show'])->name('logs.show');
});

// ── Terminal de marca web (sin auth, acceso por red local) ─────────────────
Route::get('/terminal',                [MarcaWebController::class, 'terminal'])->name('marca-web.terminal');
Route::post('/terminal/marcar',        [MarcaWebController::class, 'marcar'])->name('marca-web.marcar');
Route::post('/terminal/identificar',   [MarcaWebController::class, 'identificar'])->name('marca-web.identificar');
Route::post('/terminal/confirmar',     [MarcaWebController::class, 'confirmar'])->name('marca-web.confirmar');
Route::post('/terminal/solicitar',     [MarcaWebController::class, 'solicitarAutorizacion'])->name('marca-web.solicitar');
