<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esDiasCorridos(): bool { return $this->computo === 'dias_corridos'; }
    public function esDiasHabiles(): bool  { return $this->computo === 'dias_habiles'; }
    public function afectaUsuario(): bool  { return $this->afecta === 'usuario'; }
}
