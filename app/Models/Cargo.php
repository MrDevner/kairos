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
        'descripcion',
        'horas_semanales',
        'horas_mensuales',
        'tipo',
        'id_institucion',
        'activo',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'horas_semanales'  => 'decimal:2',
            'horas_mensuales'  => 'decimal:2',
            'activo'           => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
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

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
