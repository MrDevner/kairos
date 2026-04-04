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
        'fecha_inicio',
        'fecha_fin',
        'tipo',
        'hora_desde',
        'hora_hasta',
        'afecta_computo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio'   => 'date',
            'fecha_fin'      => 'date',
            'afecta_computo' => 'boolean',
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esGeneral(): bool
    {
        return is_null($this->id_institucion);
    }

    public function esMultiDia(): bool
    {
        return $this->fecha_fin && !$this->fecha_inicio->isSameDay($this->fecha_fin);
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

    /**
     * Eventos propios de una institución (sin herencia ni generales).
     */
    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    /**
     * Eventos visibles para una institución:
     *  - generales (id_institucion IS NULL)
     *  - asignados a esta institución o a cualquiera de sus ancestros
     */
    public function scopeVisiblesParaInstitucion(Builder $query, int $instId): Builder
    {
        $inst = \App\Models\Institucion::find($instId);
        $ids  = $inst ? $inst->idsAncestoresYPropio() : [$instId];

        return $query->where(fn ($q) =>
            $q->whereNull('id_institucion')
              ->orWhereIn('id_institucion', $ids)
        );
    }

    public function scopeEnFecha(Builder $query, string $fecha): Builder
    {
        return $query->where('fecha_inicio', '<=', $fecha)
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $fecha));
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeAfectanComputo(Builder $query): Builder
    {
        return $query->where('afecta_computo', true);
    }

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

    public function esParo(): bool
    {
        return $this->tipo === 'paro';
    }
}
