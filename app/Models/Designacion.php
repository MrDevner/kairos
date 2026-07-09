<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Designacion extends BaseModel
{
    protected $table = 'designaciones';

    protected $fillable = [
        'id_usuario',
        'id_cargo',
        'id_institucion',
        'id_dependencia',
        'fecha_inicio',
        'fecha_fin',
        'resolucion',
        'horas_semanales_efectivas',
        'activa',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'fecha_inicio'             => 'date',
            'fecha_fin'                => 'date',
            'horas_semanales_efectivas' => 'decimal:2',
            'activa'                   => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function declaracionesJuradas(): HasMany
    {
        return $this->hasMany(DeclaracionJurada::class, 'id_designacion');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Designaciones vigentes: activa=true, fecha_inicio <= hoy, fecha_fin null o >= hoy.
     */
    public function scopeVigente(Builder $query): Builder
    {
        $hoy = Carbon::today();

        return $query->where('activa', true)
            ->where('fecha_inicio', '<=', $hoy)
            ->where(function (Builder $q) use ($hoy) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $hoy);
            });
    }

    public function scopePorInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    // ── Negocio ────────────────────────────────────────────────────────────

    /**
     * Devuelve las horas semanales obligatorias:
     * usa horas_semanales_efectivas si está definida, si no las del cargo.
     */
    public function horasSemanalesObligatorias(): float
    {
        if ($this->horas_semanales_efectivas !== null) {
            return (float) $this->horas_semanales_efectivas;
        }

        return (float) $this->cargo->horas_semanales;
    }

    /**
     * Valida que horas_semanales_efectivas no supere las del cargo.
     * Lanza una excepción si la validación falla.
     *
     * @throws \InvalidArgumentException
     */
    public function validarHoras(): void
    {
        if ($this->horas_semanales_efectivas === null) {
            return;
        }

        $this->loadMissing('cargo');

        if ((float) $this->horas_semanales_efectivas > (float) $this->cargo->horas_semanales) {
            throw new \InvalidArgumentException(
                "Las horas efectivas ({$this->horas_semanales_efectivas}) no pueden superar "
                . "las horas del cargo ({$this->cargo->horas_semanales})."
            );
        }
    }

    public function estaVigente(): bool
    {
        if (!$this->activa) {
            return false;
        }

        $hoy = Carbon::today();

        return $this->fecha_inicio->lte($hoy)
            && ($this->fecha_fin === null || $this->fecha_fin->gte($hoy));
    }
}
