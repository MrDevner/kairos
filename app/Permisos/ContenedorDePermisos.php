<?php

namespace App\Permisos;

use App\Models\PermisoRol;

/**
 * Agrega los permisos CRUD de todos los módulos del sistema.
 *
 * Las claves de módulo deben mantenerse en sincronía con
 * App\Http\Controllers\RolInstitucionController::MODULOS.
 */
final class ContenedorDePermisos
{
    public const INSTITUCIONES = 'instituciones';
    public const DEPENDENCIAS = 'dependencias';
    public const USUARIOS = 'usuarios';
    public const DESIGNACIONES = 'designaciones';
    public const DDJJ = 'ddjj';
    public const LICENCIAS = 'licencias';
    public const MARCAS = 'marcas';
    public const BANCO_HORAS = 'banco_horas';
    public const INFORMES = 'informes';
    public const CALENDARIO = 'calendario';
    public const AVISOS = 'avisos';
    public const DISPOSITIVOS = 'dispositivos';
    public const ROLES = 'roles';
    public const TICKETS = 'tickets';

    /** Comodín: si está presente, anula cualquier chequeo específico. */
    public const ADMINISTRADOR = '*';

    /** @var array<string, PermisoCRUD> */
    private array $permisos = [];

    private function __construct() {}

    public static function vacio(): self
    {
        return new self();
    }

    /**
     * @param iterable<PermisoRol> $permisosRol
     */
    public static function crearDesdePermisos(iterable $permisosRol): self
    {
        $contenedor = new self();

        foreach ($permisosRol as $permisoRol) {
            $contenedor->agregarPermiso($permisoRol);
        }

        return $contenedor;
    }

    public function agregarPermiso(PermisoRol $permisoRol, ModoDeFusion $modo = ModoDeFusion::UNIR): static
    {
        $clave = $permisoRol->modulo;
        $nuevo = $permisoRol->permisoCRUD();

        if (! isset($this->permisos[$clave]) || $modo === ModoDeFusion::REEMPLAZAR) {
            $this->permisos[$clave] = $nuevo;

            return $this;
        }

        $this->permisos[$clave] = match ($modo) {
            ModoDeFusion::UNIR => $this->permisos[$clave]->unir($nuevo),
            ModoDeFusion::INTERSECTAR => $this->permisos[$clave]->intersectar($nuevo),
            ModoDeFusion::REEMPLAZAR => $nuevo,
        };

        return $this;
    }

    /**
     * Si existe el comodín "*", se devuelve siempre, sin importar la clave pedida.
     * Si el módulo no está definido, devuelve un permiso fail-closed (sin ningún acceso).
     */
    public function leerPermiso(string $clave): PermisoCRUD
    {
        if (isset($this->permisos[self::ADMINISTRADOR])) {
            return $this->permisos[self::ADMINISTRADOR];
        }

        return $this->permisos[$clave] ?? PermisoCRUD::ninguno();
    }

    /** Fusiona (OR) otro contenedor completo dentro de este, módulo a módulo. */
    public function unirPermisos(self $otro): static
    {
        foreach ($otro->permisos as $clave => $permisoCRUD) {
            $this->permisos[$clave] = isset($this->permisos[$clave])
                ? $this->permisos[$clave]->unir($permisoCRUD)
                : $permisoCRUD;
        }

        return $this;
    }

    public function instituciones(): PermisoCRUD
    {
        return $this->leerPermiso(self::INSTITUCIONES);
    }

    public function dependencias(): PermisoCRUD
    {
        return $this->leerPermiso(self::DEPENDENCIAS);
    }

    public function usuarios(): PermisoCRUD
    {
        return $this->leerPermiso(self::USUARIOS);
    }

    public function designaciones(): PermisoCRUD
    {
        return $this->leerPermiso(self::DESIGNACIONES);
    }

    public function ddjj(): PermisoCRUD
    {
        return $this->leerPermiso(self::DDJJ);
    }

    public function licencias(): PermisoCRUD
    {
        return $this->leerPermiso(self::LICENCIAS);
    }

    public function marcas(): PermisoCRUD
    {
        return $this->leerPermiso(self::MARCAS);
    }

    public function bancoHoras(): PermisoCRUD
    {
        return $this->leerPermiso(self::BANCO_HORAS);
    }

    public function informes(): PermisoCRUD
    {
        return $this->leerPermiso(self::INFORMES);
    }

    public function calendario(): PermisoCRUD
    {
        return $this->leerPermiso(self::CALENDARIO);
    }

    public function avisos(): PermisoCRUD
    {
        return $this->leerPermiso(self::AVISOS);
    }

    public function dispositivos(): PermisoCRUD
    {
        return $this->leerPermiso(self::DISPOSITIVOS);
    }

    public function roles(): PermisoCRUD
    {
        return $this->leerPermiso(self::ROLES);
    }

    public function tickets(): PermisoCRUD
    {
        return $this->leerPermiso(self::TICKETS);
    }

    public function administrador(): PermisoCRUD
    {
        return $this->leerPermiso(self::ADMINISTRADOR);
    }
}
