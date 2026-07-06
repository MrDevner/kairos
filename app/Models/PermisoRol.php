<?php

namespace App\Models;

use App\Permisos\PermisoCRUD;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoRol extends BaseModel
{
    protected $table = 'permisos_rol';

    protected $fillable = [
        'id_rol_institucion',
        'modulo',
        'puede_ver',
        'puede_crear',
        'puede_editar',
        'puede_eliminar',
        'control',
    ];

    protected function casts(): array
    {
        return [
            'puede_ver'      => 'boolean',
            'puede_crear'    => 'boolean',
            'puede_editar'   => 'boolean',
            'puede_eliminar' => 'boolean',
        ];
    }

    // --- Relaciones ---

    public function rolInstitucion(): BelongsTo
    {
        return $this->belongsTo(RolInstitucion::class, 'id_rol_institucion');
    }

    // --- Helpers ---

    public function puede(string $accion): bool
    {
        return match ($accion) {
            'ver'      => $this->puede_ver,
            'crear'    => $this->puede_crear,
            'editar'   => $this->puede_editar,
            'eliminar' => $this->puede_eliminar,
            default    => false,
        };
    }

    /** Envuelve las 4 columnas booleanas en el value object PermisoCRUD. */
    public function permisoCRUD(): PermisoCRUD
    {
        return PermisoCRUD::desdeArray([
            'create' => $this->puede_crear,
            'read'   => $this->puede_ver,
            'update' => $this->puede_editar,
            'delete' => $this->puede_eliminar,
        ]);
    }

    /** Fusiona (OR) este permiso con otro del mismo módulo. */
    public function unir(self $otro): PermisoCRUD
    {
        return $this->permisoCRUD()->unir($otro->permisoCRUD());
    }
}
