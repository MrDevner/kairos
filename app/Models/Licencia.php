<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Licencia extends BaseModel
{
    protected $table = 'licencias';

    protected $fillable = [
        'id_usuario',
        'id_designacion',
        'id_tipo_licencia',
        'fecha_inicio',
        'fecha_fin',
        'dias_computados',
        'estado',
        'motivo',
        'documentacion',
        'id_registrado_por',
        'id_aprobado_por',
        'fecha_aprobacion',
        'observaciones_aprobacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio'     => 'date',
            'fecha_fin'        => 'date',
            'fecha_aprobacion' => 'datetime',
            'dias_computados'  => 'integer',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function designacion(): BelongsTo
    {
        return $this->belongsTo(Designacion::class, 'id_designacion');
    }

    public function tipoLicencia(): BelongsTo
    {
        return $this->belongsTo(TipoLicencia::class, 'id_tipo_licencia');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_registrado_por');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_aprobado_por');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDeUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeEnEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    /**
     * Licencias vigentes en una fecha: aprobadas y el rango cubre la fecha.
     */
    public function scopeVigentesEnFecha(Builder $query, string|Carbon $fecha): Builder
    {
        $fecha = Carbon::parse($fecha)->toDateString();

        return $query->where('estado', 'aprobada')
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->where(function (Builder $q) use ($fecha) {
                $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fecha);
            });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function estaPendiente(): bool  { return $this->estado === 'pendiente'; }
    public function estaAprobada(): bool   { return $this->estado === 'aprobada'; }
    public function estaRechazada(): bool  { return $this->estado === 'rechazada'; }
}
