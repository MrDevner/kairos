<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MarcaOriginal extends BaseModel
{
    protected $table = 'marcas_originales';

    protected $fillable = [
        'id_usuario',
        'id_dispositivo',
        'fecha_hora',
        'tipo_captura',
        'datos_raw',
        'procesada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'datos_raw'  => 'array',
            'procesada'  => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'id_dispositivo');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeNoProcesadas(Builder $query): Builder
    {
        return $query->where('procesada', false);
    }

    public function scopeDeUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeEnFecha(Builder $query, string|Carbon $fecha): Builder
    {
        $fecha = Carbon::parse($fecha)->toDateString();
        return $query->whereDate('fecha_hora', $fecha);
    }

    public function scopeEnRango(Builder $query, string|Carbon $desde, string|Carbon $hasta): Builder
    {
        return $query->whereBetween('fecha_hora', [
            Carbon::parse($desde)->startOfDay(),
            Carbon::parse($hasta)->endOfDay(),
        ]);
    }
}
