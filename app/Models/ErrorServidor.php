<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * No extiende BaseModel: registrar cada bump de occurrences_count (que puede
 * suceder muchas veces por minuto en un error recurrente) generaría ruido y
 * expondría stack traces en activity_log. Las acciones de triage relevantes
 * (cambio de estado, asignación, notas) se auditan a mano en el controller.
 */
class ErrorServidor extends Model
{
    use HasUuids;

    public const ESTADOS_ACTIVOS = ['abierto', 'en_revision'];
    public const ESTADOS_CERRADOS = ['mitigado', 'solucionado'];

    protected $table = 'errores_servidor';

    protected $fillable = [
        'id_correlacion',
        'endpoint',
        'metodo_http',
        'id_usuario',
        'direccion_ip',
        'agente_usuario',
        'parametros_solicitud',
        'mensaje_error',
        'clase_error',
        'traza_pila',
        'archivo',
        'linea',
        'huella_error',
        'cantidad_ocurrencias',
        'estado',
        'id_asignado_a',
        'notas',
        'ultima_ocurrencia_en',
        'resuelto_en',
    ];

    protected function casts(): array
    {
        return [
            'parametros_solicitud' => 'array',
            'notas'                => 'array',
            'linea'                => 'integer',
            'cantidad_ocurrencias' => 'integer',
            'ultima_ocurrencia_en' => 'datetime',
            'resuelto_en'          => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_asignado_a');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_ACTIVOS);
    }

    public function scopeCerrados(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_CERRADOS);
    }

    /** Agrega una nota a la bitácora (append, sin edición/borrado individual). */
    public function agregarNota(Usuario $usuario, string $contenido): void
    {
        $notas = $this->notas ?? [];

        $notas[] = [
            'id'         => (string) \Illuminate\Support\Str::uuid(),
            'user_id'    => $usuario->id,
            'user_name'  => $usuario->nombre_completo,
            'content'    => $contenido,
            'created_at' => now()->toIso8601String(),
        ];

        $this->notas = $notas;
    }
}
