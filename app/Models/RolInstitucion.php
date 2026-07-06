<?php

namespace App\Models;

use App\Permisos\ContenedorDePermisos;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RolInstitucion extends BaseModel
{
    protected $table = 'roles_institucion';

    /**
     * Jerarquía de roles institucionales (nivel → nombre).
     * Número más bajo = mayor autoridad.
     * Un rol solo puede gestionar roles con nivel estrictamente mayor al propio.
     */
    public const JERARQUIA = [
        10  => 'Director de Institución',
        20  => 'Director Administrativo',
        30  => 'Jefe de Personal',
        40  => 'Departamento Personal',
    ];

    /** Nivel mínimo que puede gestionar roles de otros usuarios (asignar/revocar). */
    public const NIVEL_GESTION = 40;

    protected $fillable = ['nombre', 'descripcion', 'activo', 'nivel'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'nivel'  => 'integer',
        ];
    }

    // --- Helpers jerárquicos ---

    /**
     * Devuelve el nivel más alto (número menor) de un usuario en una institución.
     * Retorna PHP_INT_MAX si no tiene ningún rol vigente.
     */
    public static function nivelMinimoDeUsuario(int $usuarioId, int $instId): int
    {
        $nivel = \DB::table('roles_institucion_usuario as riu')
            ->join('roles_institucion as ri', 'ri.id', '=', 'riu.id_rol_institucion')
            ->where('riu.id_usuario', $usuarioId)
            ->where('riu.id_institucion', $instId)
            ->where('riu.activo', true)
            ->where('riu.fecha_desde', '<=', now())
            ->where(fn ($q) => $q->whereNull('riu.fecha_hasta')->orWhere('riu.fecha_hasta', '>=', now()))
            ->min('ri.nivel');

        return $nivel ?? PHP_INT_MAX;
    }

    // --- Relaciones ---

    public function asignaciones(): HasMany
    {
        return $this->hasMany(RolInstitucionUsuario::class, 'id_rol_institucion');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(PermisoRol::class, 'id_rol_institucion');
    }

    // --- Helpers ---

    /**
     * Devuelve los permisos del rol para un módulo específico.
     */
    public function permisosParaModulo(string $modulo): ?PermisoRol
    {
        return $this->permisos()->where('modulo', $modulo)->first();
    }

    /**
     * Sincroniza los permisos CRUD de un módulo para este rol.
     */
    public function establecerPermisos(string $modulo, array $permisos): PermisoRol
    {
        return $this->permisos()->updateOrCreate(
            ['modulo' => $modulo],
            array_merge(['puede_ver' => false, 'puede_crear' => false, 'puede_editar' => false, 'puede_eliminar' => false], $permisos)
        );
    }

    /** Agrega los permisos de este rol en un ContenedorDePermisos. */
    public function contenedorDePermisos(): ContenedorDePermisos
    {
        return ContenedorDePermisos::crearDesdePermisos($this->permisos);
    }
}
