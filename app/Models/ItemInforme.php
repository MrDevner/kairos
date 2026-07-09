<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemInforme extends BaseModel
{
    protected $table = 'items_informe';

    protected $fillable = [
        'id_informe_diario',
        'id_usuario',
        'id_designacion',
        'id_marca_computada',
        'tipo_novedad',
        'detalle',
        'hora_entrada',
        'hora_salida',
        'minutos_trabajados',
        'razon_ausencia',
        'requiere_atencion',
    ];

    protected function casts(): array
    {
        return [
            'minutos_trabajados' => 'integer',
            'requiere_atencion'  => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function informe(): BelongsTo
    {
        return $this->belongsTo(InformeDiario::class, 'id_informe_diario');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function designacion(): BelongsTo
    {
        return $this->belongsTo(Designacion::class, 'id_designacion');
    }

    public function marcaComputada(): BelongsTo
    {
        return $this->belongsTo(MarcaComputada::class, 'id_marca_computada');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeRequierenAtencion(Builder $query): Builder
    {
        return $query->where('requiere_atencion', true);
    }

    public function scopeTipoNovedad(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_novedad', $tipo);
    }

    // ── Helpers — color visual ─────────────────────────────────────────────

    public function colorClase(): string
    {
        return match ($this->tipo_novedad) {
            'error_atencion_urgente'              => 'danger',
            'tardanza'                            => 'warning',
            'presente'                            => 'success',
            'licencia', 'feriado', 'suspension'   => 'info',
            'paro'                                => 'orange', // naranja custom
            default                               => 'secondary',
        };
    }
}
