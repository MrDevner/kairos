<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InformeDiario extends BaseModel
{
    protected $table = 'informes_diarios';

    protected $fillable = [
        'id_institucion',
        'fecha',
        'generado_en',
        'estado',
        'id_generado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha'        => 'date',
            'generado_en'  => 'datetime',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_generado_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemInforme::class, 'id_informe_diario');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    public function scopeEnEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function tieneErroresUrgentes(): bool
    {
        return $this->items()->where('requiere_atencion', true)->exists();
    }
}
