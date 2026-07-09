<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BancoHoras extends BaseModel
{
    protected $table = 'bancos_horas';

    protected $fillable = [
        'id_usuario',
        'id_designacion',
        'saldo_minutos',
        'autorizado_acumular',
        'autorizado_negativo',
    ];

    protected function casts(): array
    {
        return [
            'saldo_minutos'       => 'integer',
            'autorizado_acumular' => 'boolean',
            'autorizado_negativo' => 'boolean',
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

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoBancoHoras::class, 'id_banco_horas');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function saldoHoras(): float
    {
        return round($this->saldo_minutos / 60, 2);
    }

    public function estaEnNegativo(): bool
    {
        return $this->saldo_minutos < 0;
    }
}
