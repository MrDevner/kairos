<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MarcaComputada extends BaseModel
{
    protected $table = 'marcas_computadas';

    protected $fillable = [
        'id_usuario',
        'id_designacion',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'id_marca_original_entrada',
        'id_marca_original_salida',
        'tipo',
        'minutos_trabajados',
        'minutos_obligatorios',
        'minutos_extra',
        'minutos_faltantes',
        'tiempo_extra_autorizado',
        'tiene_error',
        'tiene_observacion',
        'errores',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha'                   => 'date',
            'minutos_trabajados'      => 'integer',
            'minutos_obligatorios'    => 'integer',
            'minutos_extra'           => 'integer',
            'minutos_faltantes'       => 'integer',
            'tiempo_extra_autorizado' => 'boolean',
            'tiene_error'             => 'boolean',
            'tiene_observacion'       => 'boolean',
            'errores'                 => 'array',
            'observaciones'           => 'array',
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

    public function marcaEntrada(): BelongsTo
    {
        return $this->belongsTo(MarcaOriginal::class, 'id_marca_original_entrada');
    }

    public function marcaSalida(): BelongsTo
    {
        return $this->belongsTo(MarcaOriginal::class, 'id_marca_original_salida');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDeUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeEnFecha(Builder $query, string|Carbon $fecha): Builder
    {
        return $query->whereDate('fecha', Carbon::parse($fecha)->toDateString());
    }

    public function scopeEnRango(Builder $query, string|Carbon $desde, string|Carbon $hasta): Builder
    {
        return $query->whereBetween('fecha', [
            Carbon::parse($desde)->toDateString(),
            Carbon::parse($hasta)->toDateString(),
        ]);
    }

    public function scopeConErrores(Builder $query): Builder
    {
        return $query->where('tiene_error', true);
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function agregarError(string $error): void
    {
        $errores = $this->errores ?? [];
        $errores[] = $error;
        $this->errores     = $errores;
        $this->tiene_error = true;
    }

    public function agregarObservacion(string $obs): void
    {
        $observaciones = $this->observaciones ?? [];
        $observaciones[] = $obs;
        $this->observaciones    = $observaciones;
        $this->tiene_observacion = true;
    }
}
