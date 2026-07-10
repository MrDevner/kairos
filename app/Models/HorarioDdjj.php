<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioDdjj extends BaseModel
{
    protected $table = 'horarios_ddjj';

    protected $fillable = [
        'id_declaracion_jurada',
        'dia_semana',
        'hora_entrada',
        'hora_salida',
        'modalidad',
        'id_institucion_externa',
        'id_dependencia',
        'id_edificio',
        'id_oficina',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function declaracionJurada(): BelongsTo
    {
        return $this->belongsTo(DeclaracionJurada::class, 'id_declaracion_jurada');
    }

    public function institucionExterna(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion_externa');
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function edificio(): BelongsTo
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'id_oficina');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Duración del bloque horario en minutos.
     */
    public function duracionMinutos(): int
    {
        [$hE, $mE] = explode(':', $this->hora_entrada);
        [$hS, $mS] = explode(':', $this->hora_salida);
        return ((int)$hS * 60 + (int)$mS) - ((int)$hE * 60 + (int)$mE);
    }

    /**
     * Verifica si este bloque se superpone con otro (mismo día).
     */
    public function seSuperponeCon(self $otro): bool
    {
        if ($this->dia_semana !== $otro->dia_semana) {
            return false;
        }

        $entrada1 = $this->hora_entrada;
        $salida1  = $this->hora_salida;
        $entrada2 = $otro->hora_entrada;
        $salida2  = $otro->hora_salida;

        return $entrada1 < $salida2 && $entrada2 < $salida1;
    }
}
