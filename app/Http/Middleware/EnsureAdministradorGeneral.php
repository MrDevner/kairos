<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso exclusivamente al Administrador General
 * (usuarios con el comodín "*" en sus permisos).
 *
 * Uso en rutas (sin necesidad de alias):
 *   ->middleware(\App\Http\Middleware\EnsureAdministradorGeneral::class)
 */
class EnsureAdministradorGeneral
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            abort(401);
        }

        if (! $usuario->permisos()->administrador()->tieneTodosLosPermisos()) {
            abort(403, 'Esta sección está reservada al Administrador General.');
        }

        return $next($request);
    }
}
