<?php

namespace App\Permisos;

/**
 * Value object inmutable con los 4 flags CRUD de un módulo.
 * Es la unidad atómica del sistema de permisos: nunca viajan sueltos.
 */
final class PermisoCRUD
{
    public function __construct(
        private readonly bool $crear = false,
        private readonly bool $leer = false,
        private readonly bool $actualizar = false,
        private readonly bool $eliminar = false,
    ) {}

    /**
     * @param array{create?: bool, read?: bool, update?: bool, delete?: bool} $datos
     */
    public static function desdeArray(array $datos): self
    {
        return new self(
            crear: (bool) ($datos['create'] ?? false),
            leer: (bool) ($datos['read'] ?? false),
            actualizar: (bool) ($datos['update'] ?? false),
            eliminar: (bool) ($datos['delete'] ?? false),
        );
    }

    /** Permiso fail-closed: ningún flag habilitado. */
    public static function ninguno(): self
    {
        return new self();
    }

    /** Permiso con los 4 flags habilitados (usado por el comodín administrador). */
    public static function todos(): self
    {
        return new self(true, true, true, true);
    }

    public function create(): bool
    {
        return $this->crear;
    }

    public function read(): bool
    {
        return $this->leer;
    }

    public function update(): bool
    {
        return $this->actualizar;
    }

    public function delete(): bool
    {
        return $this->eliminar;
    }

    public function tieneAlgunPermiso(): bool
    {
        return $this->crear || $this->leer || $this->actualizar || $this->eliminar;
    }

    public function tieneTodosLosPermisos(): bool
    {
        return $this->crear && $this->leer && $this->actualizar && $this->eliminar;
    }

    /** Combina flag a flag con OR. Más roles solo puede sumar acceso, nunca restarlo. */
    public function unir(self $otro): self
    {
        return new self(
            crear: $this->crear || $otro->crear,
            leer: $this->leer || $otro->leer,
            actualizar: $this->actualizar || $otro->actualizar,
            eliminar: $this->eliminar || $otro->eliminar,
        );
    }

    /** Combina flag a flag con AND (permiso más restrictivo). */
    public function intersectar(self $otro): self
    {
        return new self(
            crear: $this->crear && $otro->crear,
            leer: $this->leer && $otro->leer,
            actualizar: $this->actualizar && $otro->actualizar,
            eliminar: $this->eliminar && $otro->eliminar,
        );
    }
}
