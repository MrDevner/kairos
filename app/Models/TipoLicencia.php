<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoLicencia extends BaseModel
{
    protected $table = 'tipos_licencia';

    protected $fillable = [
        'nombre',
        'descripcion',
        'computo',
        'afecta',
        'dias_maximos',
        'requiere_documentacion',
        'activo',
        'id_institucion',
        'id_categoria_cargo',
    ];

    protected function casts(): array
    {
        return [
            'requiere_documentacion' => 'boolean',
            'activo'                 => 'boolean',
            'dias_maximos'           => 'integer',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function licencias(): HasMany
    {
        return $this->hasMany(Licencia::class, 'id_tipo_licencia');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function categoriaCargo(): BelongsTo
    {
        return $this->belongsTo(CategoriaCargo::class, 'id_categoria_cargo');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Tipos permitidos para avisos en una institución.
     * Si la institución configuró una lista → filtra por ella.
     * Si no configuró nada → devuelve todos los visibles (sin restricción adicional).
     */
    public function scopePermitidosParaAvisoEnInstitucion(Builder $query, int $instId): Builder
    {
        $this->scopeVisiblesParaInstitucion($query, $instId);

        $idsConfigurados = \DB::table('aviso_licencias_permitidas')
            ->where('id_institucion', $instId)
            ->pluck('id_tipo_licencia');

        if ($idsConfigurados->isNotEmpty()) {
            $query->whereIn('tipos_licencia.id', $idsConfigurados);
        }

        return $query;
    }

    /**
     * Tipos visibles para una institución: los globales (sin institución) más
     * los asignados a cualquier ancestro de la institución (inclusive ella misma).
     */
    public function scopeVisiblesParaInstitucion(Builder $query, int $instId): Builder
    {
        $inst = Institucion::find($instId);
        $ids  = $inst ? $inst->idsAncestoresYPropio() : [$instId];

        return $query->where(function (Builder $q) use ($ids) {
            $q->whereNull('id_institucion')
              ->orWhereIn('id_institucion', $ids);
        });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esDiasCorridos(): bool { return $this->computo === 'dias_corridos'; }
    public function esDiasHabiles(): bool  { return $this->computo === 'dias_habiles'; }
    public function afectaUsuario(): bool  { return $this->afecta === 'usuario'; }
    public function aplicaATodos(): bool   { return is_null($this->id_categoria_cargo); }
}
