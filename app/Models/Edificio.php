<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edificio extends BaseModel
{
    protected $table = 'edificios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_institucion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function oficinas(): HasMany
    {
        return $this->hasMany(Oficina::class, 'id_edificio');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }
}
