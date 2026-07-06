<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAdjunto extends BaseModel
{
    protected $table = 'ticket_adjuntos';

    protected $fillable = ['id_ticket', 'id_usuario', 'nombre_original', 'ruta', 'tipo_mime', 'tamanio'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
