<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondicionEvento extends BaseModel
{
    protected $table = 'condiciones_evento';

    protected $fillable = [
        'id_evento_calendario',
        'tipo_condicion',
        'valor_condicion',
        'efecto',
        'minutos_afectados',
    ];

    protected function casts(): array
    {
        return [
            'minutos_afectados' => 'integer',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function evento(): BelongsTo
    {
        return $this->belongsTo(EventoCalendario::class, 'id_evento_calendario');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Evalúa si esta condición aplica al usuario/designación dados.
     */
    public function aplicaA(Usuario $usuario, Designacion $designacion): bool
    {
        return match ($this->tipo_condicion) {
            'sexo'            => $usuario->sexo === $this->valor_condicion,
            'cargo'           => (string) $designacion->id_cargo === $this->valor_condicion,
            'dependencia'     => (string) $designacion->id_dependencia === $this->valor_condicion,
            'categoria_cargo' => (string) $designacion->cargo?->id_categoria === $this->valor_condicion,
            'custom'          => false, // extensible por lógica de negocio específica
            default           => false,
        };
    }
}
