<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oficina extends BaseModel
{
    protected $table = 'oficinas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_edificio',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function edificio(): BelongsTo
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDeEdificio(Builder $query, int $idEdificio): Builder
    {
        return $query->where('id_edificio', $idEdificio);
    }
}
