<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Jefatura extends BaseModel
{
    protected $table = 'jefaturas';

    protected $fillable = [
        'id_dependencia',
        'id_usuario',
        'cargo',
        'fecha_desde',
        'fecha_hasta',
        'activa',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'activa'      => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Jefaturas vigentes: activa=true, fecha_desde <= hoy, fecha_hasta null o >= hoy.
     */
    public function scopeVigente(Builder $query): Builder
    {
        $hoy = Carbon::today();

        return $query->where('activa', true)
            ->where('fecha_desde', '<=', $hoy)
            ->where(function (Builder $q) use ($hoy) {
                $q->whereNull('fecha_hasta')
                    ->orWhere('fecha_hasta', '>=', $hoy);
            });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function estaVigente(): bool
    {
        if (!$this->activa) {
            return false;
        }

        $hoy = Carbon::today();

        return $this->fecha_desde->lte($hoy)
            && ($this->fecha_hasta === null || $this->fecha_hasta->gte($hoy));
    }
}
