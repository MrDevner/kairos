<?php

namespace App\Models;

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
}
