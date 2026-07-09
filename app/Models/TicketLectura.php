<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketLectura extends BaseModel
{
    protected $table = 'ticket_lecturas';

    public $timestamps = false;

    protected $fillable = ['id_ticket', 'id_usuario', 'leido_en'];

    protected function casts(): array
    {
        return ['leido_en' => 'datetime'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /** Upsert de la marca de lectura: es lo que alimenta el contador de no-leídos. */
    public static function marcarLeido(Ticket $ticket, User $usuario): void
    {
        self::updateOrCreate(
            ['id_ticket' => $ticket->id, 'id_usuario' => $usuario->id],
            ['leido_en' => now()]
        );
    }
}
