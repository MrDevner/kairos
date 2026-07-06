<?php

use App\Support\ServerErrorLogger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role.institucion' => \App\Http\Middleware\CheckInstitutionRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            ServerErrorLogger::log($e, request());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($statusCode < 500 || $request->expectsJson()) {
                return null;
            }

            $esAdmin = $request->user()?->permisos()->administrador()->tieneTodosLosPermisos() ?? false;

            return response()->view('errors.500', [
                'correlationId' => $request->attributes->get('correlation_id'),
                'exception'     => $esAdmin ? $e : null,
            ], $statusCode);
        });
    })->create();
