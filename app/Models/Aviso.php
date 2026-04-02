<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aviso extends BaseModel
{
    protected $table = 'avisos';

    protected $fillable = [
        'id_usuario',
        'id_designacion',
        'id_institucion',
        'tipo',
        'fecha',
        'hora_estimada_llegada',
        'motivo',
        'id_registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
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

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_registrado_por');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDeUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    public function scopeDeDependencia(Builder $query, int $idDependencia): Builder
    {
        return $query->whereHas(
            'designacion',
            fn (Builder $q) => $q->where('id_dependencia', $idDependencia)
        );
    }

    public function scopeEnFecha(Builder $query, string $fecha): Builder
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function scopeEnRango(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function loRegistroElPropioUsuario(): bool
    {
        return $this->id_registrado_por === $this->id_usuario;
    }
}
