<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class RolInstitucionUsuario extends BaseModel
{
    protected $table = 'roles_institucion_usuario';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_rol_institucion',
        'id_institucion',
        'activo',
        'fecha_desde',
        'fecha_hasta',
        'id_asignado_por',
        'control',
    ];

    protected function casts(): array
    {
        return [
            'activo'      => 'boolean',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
        ];
    }

    // --- Relaciones ---

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function rolInstitucion(): BelongsTo
    {
        return $this->belongsTo(RolInstitucion::class, 'id_rol_institucion');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    /** Usuario que otorgó esta asignación de rol (auditoría). */
    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_asignado_por');
    }

    // --- Scopes ---

    /**
     * Filtra asignaciones vigentes (activas y dentro del período).
     */
    public function scopeVigente(Builder $query): Builder
    {
        $hoy = Carbon::today();

        return $query
            ->where('activo', true)
            ->where('fecha_desde', '<=', $hoy)
            ->where(function (Builder $q) use ($hoy) {
                $q->whereNull('fecha_hasta')->orWhere('fecha_hasta', '>=', $hoy);
            });
    }

    public function scopeDeInstitucion(Builder $query, int $institucionId): Builder
    {
        return $query->where('id_institucion', $institucionId);
    }

    /**
     * Asignaciones globales (id_institucion null, aplican a cualquier institución,
     * ej. el comodín administrador) o de la institución indicada.
     */
    public function scopeGlobalODeInstitucion(Builder $query, ?int $institucionId): Builder
    {
        return $query->where(function (Builder $q) use ($institucionId) {
            $q->whereNull('id_institucion');

            if ($institucionId !== null) {
                $q->orWhere('id_institucion', $institucionId);
            }
        });
    }

    // --- Helpers ---

    public function estaVigente(): bool
    {
        if (! $this->activo) {
            return false;
        }

        $hoy = Carbon::today();

        return $this->fecha_desde <= $hoy
            && ($this->fecha_hasta === null || $this->fecha_hasta >= $hoy);
    }
}
