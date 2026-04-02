<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoBancoHoras extends BaseModel
{
    protected $table = 'movimientos_banco_horas';

    protected $fillable = [
        'id_banco_horas',
        'fecha',
        'tipo',
        'minutos',
        'motivo',
        'id_marca_computada',
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

    public function banco(): BelongsTo
    {
        return $this->belongsTo(BancoHoras::class, 'id_banco_horas');
    }

    public function marcaComputada(): BelongsTo
    {
        return $this->belongsTo(MarcaComputada::class, 'id_marca_computada');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_registrado_por');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeEnRango(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }
}
