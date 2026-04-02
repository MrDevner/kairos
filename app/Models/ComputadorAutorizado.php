<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComputadorAutorizado extends BaseModel
{
    protected $table = 'computadores_autorizados';

    protected $fillable = [
        'id_dispositivo',
        'fingerprint',
        'nombre_equipo',
        'id_dependencia',
        'autorizado',
    ];

    protected function casts(): array
    {
        return ['autorizado' => 'boolean'];
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'id_dispositivo');
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }
}
