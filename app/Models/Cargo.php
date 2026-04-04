<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends BaseModel
{
    protected $table = 'cargos';

    protected $fillable = [
        'nombre',
        'horas_semanales',
        'indice',
        'id_categoria',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'horas_semanales' => 'decimal:2',
            'indice'          => 'decimal:4',
            'activo'          => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaCargo::class, 'id_categoria');
    }

    public function designaciones(): HasMany
    {
        return $this->hasMany(Designacion::class, 'id_cargo');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
