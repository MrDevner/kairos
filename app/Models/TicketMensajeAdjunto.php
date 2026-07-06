<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMensajeAdjunto extends BaseModel
{
    protected $table = 'ticket_mensaje_adjuntos';

    protected $fillable = ['id_ticket_mensaje', 'nombre_original', 'ruta', 'tipo_mime', 'tamanio'];

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(TicketMensaje::class, 'id_ticket_mensaje');
    }
}
