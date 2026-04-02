<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeclaracionJurada extends BaseModel
{
    protected $table = 'declaraciones_juradas';

    protected $fillable = [
        'id_usuario',
        'id_designacion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function designacion(): BelongsTo
    {
        return $this->belongsTo(Designacion::class, 'id_designacion');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioDdjj::class, 'id_declaracion_jurada');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['borrador', 'presentada', 'aprobada'])
            ->where(function (Builder $q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now()->toDateString());
            });
    }

    public function scopeDeUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeDeDesignacion(Builder $query, int $idDesignacion): Builder
    {
        return $query->where('id_designacion', $idDesignacion);
    }

    public function scopeEnEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function estaActiva(): bool
    {
        return in_array($this->estado, ['borrador', 'presentada', 'aprobada'])
            && ($this->fecha_fin === null || $this->fecha_fin->gte(now()));
    }

    public function esBorrador(): bool   { return $this->estado === 'borrador'; }
    public function estaPresentada(): bool { return $this->estado === 'presentada'; }
    public function estaAprobada(): bool  { return $this->estado === 'aprobada'; }
    public function estaRechazada(): bool { return $this->estado === 'rechazada'; }
}
