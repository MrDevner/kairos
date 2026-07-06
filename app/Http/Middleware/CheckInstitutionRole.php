<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga uno de los roles indicados
 * en la institución activa de la sesión.
 *
 * Uso en rutas:
 *   ->middleware('role.institucion:Jefe de Personal')
 *   ->middleware('role.institucion:Administrador,Director Administrativo')
 *
 * Bypass automático para roles globales:
 *   - Administrador General: acceso total sin restricción.
 *   - Auditor General: acceso de solo lectura (la verificación de escritura
 *     debe hacerse a nivel de controlador/policy).
 */
class CheckInstitutionRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            abort(401);
        }

        // Administrador General (comodín "*") tiene acceso irrestricto
        if ($usuario->permisos()->administrador()->tieneTodosLosPermisos()) {
            return $next($request);
        }

        $institucionId = session('institucion_activa_id');

        if (! $institucionId) {
            abort(403, 'No hay institución activa seleccionada.');
        }

        if (empty($roles) || $usuario->tieneRolEnInstitucion($roles, (int) $institucionId)) {
            return $next($request);
        }

        abort(403, 'No tiene el rol requerido en esta institución.');
    }
}
