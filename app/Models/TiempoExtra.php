<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiempoExtra extends BaseModel
{
    protected $table = 'tiempos_extra';

    protected $fillable = [
        'id_usuario',
        'id_designacion',
        'fecha',
        'minutos',
        'motivo',
        'id_registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha'   => 'date',
            'minutos' => 'integer',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function designacion(): BelongsTo
    {
        return $this->belongsTo(Designacion::class, 'id_designacion');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_registrado_por');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDeUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeEnRango(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }
}
