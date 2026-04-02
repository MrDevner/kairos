<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoCalendario extends BaseModel
{
    protected $table = 'eventos_calendario';

    protected $fillable = [
        'id_institucion',
        'titulo',
        'descripcion',
        'fecha',
        'tipo',
        'hora_desde',
        'hora_hasta',
        'afecta_computo',
    ];

    protected function casts(): array
    {
        return [
            'fecha'          => 'date',
            'afecta_computo' => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function condiciones(): HasMany
    {
        return $this->hasMany(CondicionEvento::class, 'id_evento_calendario');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    public function scopeEnFecha(Builder $query, string $fecha): Builder
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeAfectanComputo(Builder $query): Builder
    {
        return $query->where('afecta_computo', true);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esSuspensionTotal(): bool
    {
        return in_array($this->tipo, ['feriado', 'suspension_total', 'dia_no_laborable']);
    }

    public function esSuspensionParcial(): bool
    {
        return $this->tipo === 'suspension_parcial';
    }

    public function esCondicional(): bool
    {
        return $this->tipo === 'evento_condicional';
    }
}
