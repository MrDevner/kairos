<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMensaje extends BaseModel
{
    protected $table = 'ticket_mensajes';

    protected $fillable = ['id_ticket', 'id_usuario', 'mensaje'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(TicketMensajeAdjunto::class, 'id_ticket_mensaje');
    }
}
