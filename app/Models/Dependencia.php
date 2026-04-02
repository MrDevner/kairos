<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dependencia extends BaseModel
{
    protected $table = 'dependencias';

    protected $fillable = [
        'nombre',
        'sigla',
        'descripcion',
        'id_institucion',
        'id_dependencia_padre',
        'activa',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia_padre');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(Dependencia::class, 'id_dependencia_padre');
    }

    public function hijosRecursivos(): HasMany
    {
        return $this->hijos()->with('hijosRecursivos');
    }

    public function jefaturas(): HasMany
    {
        return $this->hasMany(Jefatura::class, 'id_dependencia');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDependenciasActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    public function scopeRaices(Builder $query): Builder
    {
        return $query->whereNull('id_dependencia_padre');
    }

    // ── Métodos ────────────────────────────────────────────────────────────

    /**
     * Devuelve la jefatura vigente de esta dependencia, o null si no hay ninguna.
     */
    public function jefeActual(): ?Jefatura
    {
        return $this->jefaturas()->vigente()->latest('fecha_desde')->first();
    }

    /**
     * Carga y devuelve el árbol completo desde esta dependencia.
     */
    public function arbolCompleto(): static
    {
        $this->loadMissing('hijosRecursivos');
        return $this;
    }

    /**
     * Devuelve todos los IDs de la dependencia y sus descendientes.
     * Útil para filtrar datos de toda una sub-jerarquía.
     *
     * @return array<int>
     */
    public function idsConDescendientes(): array
    {
        $this->loadMissing('hijosRecursivos');
        return $this->recolectarIds($this);
    }

    private function recolectarIds(Dependencia $nodo): array
    {
        $ids = [$nodo->id];
        foreach ($nodo->hijosRecursivos as $hijo) {
            $ids = array_merge($ids, $this->recolectarIds($hijo));
        }
        return $ids;
    }

    public function esRaiz(): bool
    {
        return is_null($this->id_dependencia_padre);
    }

    /**
     * Devuelve el árbol de dependencias activas de una institución,
     * desde las raíces con hijos anidados de forma recursiva.
     *
     * @return Collection<int, static>
     */
    public static function arbolDeInstitucion(int $idInstitucion): Collection
    {
        return static::dependenciasActivas()
            ->deInstitucion($idInstitucion)
            ->raices()
            ->with('hijosRecursivos')
            ->get();
    }
}
