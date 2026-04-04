<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'id_ciudad_domicilio',
        'telefono',
        'email',
        'configuracion',
        'activa',
    ];

    /**
     * Valores por defecto para el campo configuracion.
     */
    private const CONFIG_DEFAULTS = [
        'umbral_jornada_minima'      => 60,       // minutos
        'banco_horas_por'            => 'usuario', // 'usuario' | 'designacion'
        'permite_avisos_usuario'     => false,
        'horas_extra_autorizadas'    => false,
        'roles_autorizan_licencias'  => [],        // IDs de RolInstitucion adicionales
    ];

    /** Roles que siempre pueden autorizar licencias, sin configuración adicional. */
    public const ROLES_AUTORIZAN_DEFAULT = ['Director Administrativo', 'Jefe de Personal'];

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

    public function ciudadDomicilio(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad_domicilio');
    }

    /**
     * Tipos de licencia habilitados para avisos en esta institución.
     * Si la colección está vacía → se permiten todos los visibles.
     */
    public function tiposLicenciaAviso(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoLicencia::class,
            'aviso_licencias_permitidas',
            'id_institucion',
            'id_tipo_licencia'
        );
    }

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

    /** Tipos de licencia asignados directamente a esta institución. */
    public function tiposLicencia(): HasMany
    {
        return $this->hasMany(TipoLicencia::class, 'id_institucion');
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

    /**
     * Devuelve una lista plana ordenada por jerarquía (DFS preorden).
     * Cada elemento es ['institucion' => Institucion, 'nivel' => int].
     * Si se pasa $soloIds, solo incluye los nodos cuyo id esté en esa lista.
     *
     * @param  array<int>|null $soloIds
     * @return array<int, array{institucion: static, nivel: int}>
     */
    public static function listaJerarquica(?array $soloIds = null): array
    {
        $arbol = static::activas()->raices()->with('hijasRecursivas')->orderBy('nombre')->get();
        $lista = [];
        self::aplanarArbol($arbol, 0, $soloIds, $lista);
        return $lista;
    }

    private static function aplanarArbol(Collection $nodos, int $nivel, ?array $soloIds, array &$lista): void
    {
        foreach ($nodos as $nodo) {
            if ($soloIds === null || in_array($nodo->id, $soloIds)) {
                $lista[] = ['institucion' => $nodo, 'nivel' => $nivel];
            }
            if ($nodo->hijasRecursivas->isNotEmpty()) {
                self::aplanarArbol($nodo->hijasRecursivas, $nivel + 1, $soloIds, $lista);
            }
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esRaiz(): bool
    {
        return is_null($this->id_institucion_padre);
    }

    /**
     * Verifica si un usuario puede autorizar (aprobar/rechazar) licencias
     * en esta institución.
     *
     * Regla: Admin General siempre puede. Director Administrativo y Jefe de
     * Personal siempre pueden. Cualquier rol adicional configurado en
     * `configuracion.roles_autorizan_licencias` también puede.
     */
    public function puedeAutorizarLicencias(Usuario $user): bool
    {
        if ($user->hasRole('Administrador General')) {
            return true;
        }

        foreach (self::ROLES_AUTORIZAN_DEFAULT as $rol) {
            if ($user->tieneRolEnInstitucion($rol, $this->id)) {
                return true;
            }
        }

        $adicionales = $this->configuracion['roles_autorizan_licencias'] ?? [];
        if (!empty($adicionales)) {
            return $user->rolesInstitucion()
                ->vigente()
                ->deInstitucion($this->id)
                ->whereIn('id_rol_institucion', $adicionales)
                ->exists();
        }

        return false;
    }

    /**
     * IDs de esta institución y todos sus ancestros hasta la raíz.
     * Útil para resolver visibilidad de tipos de licencia heredados.
     *
     * @return array<int>
     */
    public function idsAncestoresYPropio(): array
    {
        $ids    = [$this->id];
        $actual = $this;

        while ($actual->id_institucion_padre !== null) {
            $actual = $actual->padre;
            if ($actual) {
                $ids[] = $actual->id;
            }
        }

        return $ids;
    }

    public function getConfig(string $clave): mixed
    {
        return $this->configuracion[$clave] ?? self::CONFIG_DEFAULTS[$clave] ?? null;
    }

    /**
     * Devuelve la ruta del logo efectivo: el propio, o el del primer ancestro que tenga uno.
     * Retorna null si ninguno en la jerarquía tiene logo.
     */
    public function logoEfectivo(): ?string
    {
        if ($this->logo) {
            return $this->logo;
        }

        $actual = $this;
        while ($actual->id_institucion_padre !== null) {
            $actual = $actual->padre;
            if ($actual && $actual->logo) {
                return $actual->logo;
            }
        }

        return null;
    }
}
