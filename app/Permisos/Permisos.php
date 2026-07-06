<?php

namespace App\Permisos;

use App\Models\Usuario;

/**
 * Punto único de entrada al sistema de permisos, usado en controllers,
 * vistas y middlewares: Permisos::delUsuarioActual()->modulo()->accion().
 *
 * Distingue usuario real (autenticado) de usuario actual (personificado),
 * para que los chequeos de seguridad sensibles puedan exigir siempre el real.
 */
final class Permisos
{
    private const CLAVE_SESION_PERSONIFICADO = 'usuario_personificado_id';

    private function __construct() {}

    /** El usuario autenticado, sin importar si está personificando a otro. */
    public static function usuarioReal(): ?Usuario
    {
        return auth()->user();
    }

    /** El usuario real, o el personificado si hay una personificación activa. */
    public static function usuarioActual(): ?Usuario
    {
        $real = self::usuarioReal();

        if (! $real) {
            return null;
        }

        $idPersonificado = session(self::CLAVE_SESION_PERSONIFICADO);

        if ($idPersonificado === null) {
            return $real;
        }

        return Usuario::find($idPersonificado) ?? $real;
    }

    public static function estaPersonificando(): bool
    {
        return session(self::CLAVE_SESION_PERSONIFICADO) !== null;
    }

    public static function iniciarPersonificacion(Usuario $usuario): void
    {
        session([self::CLAVE_SESION_PERSONIFICADO => $usuario->id]);
    }

    public static function detenerPersonificacion(): void
    {
        session()->forget(self::CLAVE_SESION_PERSONIFICADO);
    }

    /** Permisos efectivos del usuario real. Usar para chequeos de seguridad sensibles. */
    public static function delUsuarioReal(): ContenedorDePermisos
    {
        return self::usuarioReal()?->permisos() ?? ContenedorDePermisos::vacio();
    }

    /** Permisos efectivos del usuario actual (personificado si aplica). Uso general en la app. */
    public static function delUsuarioActual(): ContenedorDePermisos
    {
        return self::usuarioActual()?->permisos() ?? ContenedorDePermisos::vacio();
    }
}
