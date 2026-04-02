<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RolInstitucion extends BaseModel
{
    protected $table = 'roles_institucion';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
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
}
