<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Institucion extends BaseModel
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'sigla',
        'descripcion',
        'id_institucion_padre',
        'logo',
        'direccion',
        'telefono',
        'email',
        'configuracion',
        'activa',
    ];

    /**
     * Valores por defecto para el campo configuracion.
     */
    private const CONFIG_DEFAULTS = [
        'umbral_jornada_minima'   => 60,       // minutos
        'banco_horas_por'         => 'usuario', // 'usuario' | 'designacion'
        'permite_avisos_usuario'  => false,
        'horas_extra_autorizadas' => false,
    ];

    // ── Casts ──────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    /**
     * Cast personalizado para `configuracion`:
     * fusiona los valores guardados con los defaults para garantizar
     * que siempre estén todas las claves disponibles.
     */
    protected function configuracion(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): array => array_merge(
                self::CONFIG_DEFAULTS,
                $value ? (json_decode($value, true) ?? []) : []
            ),
            set: fn (array|null $value): string => json_encode(
                array_merge(self::CONFIG_DEFAULTS, $value ?? [])
            ),
        );
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    /** Institución padre (null si es raíz). */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion_padre');
    }

    /** Instituciones hijas directas. */
    public function hijas(): HasMany
    {
        return $this->hasMany(Institucion::class, 'id_institucion_padre');
    }

    /** Dependencias directas de esta institución. */
    public function dependencias(): HasMany
    {
        return $this->hasMany(Dependencia::class, 'id_institucion');
    }

    /** Cargos definidos en esta institución. */
    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'id_institucion');
    }

    /** Designaciones activas en esta institución. */
    public function designaciones(): HasMany
    {
        return $this->hasMany(Designacion::class, 'id_institucion');
    }

    /** Eventos del calendario institucional. */
    public function eventosCalendario(): HasMany
    {
        return $this->hasMany(EventoCalendario::class, 'id_institucion');
    }

    /**
     * Hijas recursivas (carga anidada ilimitada).
     * Uso: $inst->load('hijasRecursivas') o eager-load en consultas.
     */
    public function hijasRecursivas(): HasMany
    {
        return $this->hijas()->with('hijasRecursivas');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeRaices(Builder $query): Builder
    {
        return $query->whereNull('id_institucion_padre');
    }

    // ── Métodos de árbol ───────────────────────────────────────────────────

    /**
     * Carga y devuelve el árbol completo a partir de esta institución.
     *
     * Ejemplo:
     *   $unsj = Institucion::find(1)->arbolCompleto();
     *   // $unsj->hijasRecursivas contiene toda la jerarquía anidada
     */
    public function arbolCompleto(): static
    {
        $this->loadMissing('hijasRecursivas');
        return $this;
    }

    /**
     * Devuelve la jerarquía completa de todas las instituciones activas
     * desde sus raíces, con hijas anidadas de forma recursiva.
     *
     * Ejemplo:
     *   $arbol = Institucion::arbol();
     *
     * @return Collection<int, static>
     */
    public static function arbol(): Collection
    {
        return static::activas()
            ->raices()
            ->with('hijasRecursivas')
            ->get();
    }

    /**
     * Devuelve la ruta desde la raíz hasta esta institución.
     * Ej: ['UNSJ', 'Rectorado', 'CREACOM']
     *
     * @return array<string>
     */
    public function rutaDesdeRaiz(): array
    {
        $ruta = [$this->nombre];
        $actual = $this;

        while ($actual->id_institucion_padre !== null) {
            $actual = $actual->padre;
            if ($actual) {
                array_unshift($ruta, $actual->nombre);
            }
        }

        return $ruta;
    }

    /**
     * Devuelve todos los IDs de la institución y sus descendientes.
     * Útil para filtrar datos de toda una sub-jerarquía.
     *
     * @return array<int>
     */
    public function idsConDescendientes(): array
    {
        $this->loadMissing('hijasRecursivas');
        return $this->recolectarIds($this);
    }

    private function recolectarIds(Institucion $nodo): array
    {
        $ids = [$nodo->id];
        foreach ($nodo->hijasRecursivas as $hija) {
            $ids = array_merge($ids, $this->recolectarIds($hija));
        }
        return $ids;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esRaiz(): bool
    {
        return is_null($this->id_institucion_padre);
    }

    public function getConfig(string $clave): mixed
    {
        return $this->configuracion[$clave] ?? self::CONFIG_DEFAULTS[$clave] ?? null;
    }
}
