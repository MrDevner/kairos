<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSolicitudResolucion extends BaseModel
{
    protected $table = 'ticket_solicitudes_resolucion';

    protected $fillable = ['id_ticket', 'id_usuario', 'es_solicitante', 'aprobado_en', 'estado_propuesto'];

    protected function casts(): array
    {
        return [
            'es_solicitante' => 'boolean',
            'aprobado_en'    => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
