<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ticket extends BaseModel
{
    protected $table = 'tickets';

    public const ESTADOS = ['abierto', 'en_proceso', 'resuelto', 'cerrado'];
    public const ESTADOS_ABIERTOS = ['abierto', 'en_proceso'];
    public const PRIORIDADES = ['baja', 'media', 'alta', 'urgente'];

    protected $fillable = [
        'titulo', 'descripcion', 'categoria', 'estado', 'prioridad',
        'id_creador', 'id_abierto_por', 'id_asignado_a',
        'fecha_limite', 'fecha_cierre',
        'categoria_cambio_motivo', 'id_categoria_cambiada_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'fecha_cierre' => 'datetime',
        ];
    }

    // --- Relaciones ---

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_creador');
    }

    public function abiertoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_abierto_por');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_asignado_a');
    }

    public function categoriaCambiadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_categoria_cambiada_por');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(TicketMensaje::class, 'id_ticket')->orderBy('created_at');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(TicketAdjunto::class, 'id_ticket');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(TicketLectura::class, 'id_ticket');
    }

    public function solicitudesResolucion(): HasMany
    {
        return $this->hasMany(TicketSolicitudResolucion::class, 'id_ticket');
    }

    // --- Permisos del sistema de tickets ---
    // esSoporte = admin wildcard o permiso de lectura en el módulo 'tickets'
    // (User::permisos()->tickets()->read() ya devuelve true para el comodín "*").

    public static function esSoporte(User $usuario): bool
    {
        return $usuario->permisos()->tickets()->read();
    }

    public static function puedeVerTodos(User $usuario): bool
    {
        return self::esSoporte($usuario);
    }

    public static function puedeCrear(User $usuario): bool
    {
        return $usuario->activo;
    }

    public function esParticipante(User $usuario): bool
    {
        return $this->id_creador === $usuario->id
            || $this->id_abierto_por === $usuario->id
            || $this->id_asignado_a === $usuario->id
            || $this->mensajes()->where('id_usuario', $usuario->id)->exists();
    }

    /** @return Collection<int, User> */
    public function participantes(): Collection
    {
        $ids = collect([$this->id_creador, $this->id_abierto_por, $this->id_asignado_a])
            ->merge($this->mensajes()->pluck('id_usuario'))
            ->filter()
            ->unique()
            ->values();

        return User::whereIn('id', $ids)->get();
    }

    // --- Scopes ---

    /** Si el usuario no ve todos los tickets, se filtra a los propios/asignados/participados. */
    public function scopeVisiblesPara(Builder $query, User $usuario): Builder
    {
        if (self::puedeVerTodos($usuario)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($usuario) {
            $q->where('id_creador', $usuario->id)
                ->orWhere('id_abierto_por', $usuario->id)
                ->orWhere('id_asignado_a', $usuario->id)
                ->orWhereHas('mensajes', fn (Builder $m) => $m->where('id_usuario', $usuario->id));
        });
    }

    /** Orden del listado: por estado (abierto > en_proceso > resuelto > cerrado) y prioridad. */
    public function scopeOrdenParaListado(Builder $query): Builder
    {
        return $query
            ->orderByRaw("FIELD(estado, 'abierto','en_proceso','resuelto','cerrado')")
            ->orderByRaw("FIELD(prioridad, 'urgente','alta','media','baja')")
            ->orderByDesc('created_at');
    }

    // --- Badge de no leídos ---

    /**
     * Cuenta tickets abiertos/en_proceso donde el usuario participa (o, si es
     * soporte, también los sin asignar) y que fueron creados/modificados
     * después de su última lectura registrada, más las solicitudes de
     * resolución pendientes de su aprobación.
     */
    public static function contarNoLeidosParaUsuario(User $usuario): int
    {
        $esSoporte = self::esSoporte($usuario);

        $noLeidos = self::whereIn('estado', self::ESTADOS_ABIERTOS)
            ->where(function (Builder $q) use ($usuario, $esSoporte) {
                $q->where('id_creador', $usuario->id)
                    ->orWhere('id_abierto_por', $usuario->id)
                    ->orWhere('id_asignado_a', $usuario->id)
                    ->orWhereHas('mensajes', fn (Builder $m) => $m->where('id_usuario', $usuario->id));

                if ($esSoporte) {
                    $q->orWhereNull('id_asignado_a');
                }
            })
            ->whereNotExists(function ($sub) use ($usuario) {
                $sub->select(DB::raw(1))
                    ->from('ticket_lecturas')
                    ->whereColumn('ticket_lecturas.id_ticket', 'tickets.id')
                    ->where('ticket_lecturas.id_usuario', $usuario->id)
                    ->whereColumn('ticket_lecturas.leido_en', '>=', 'tickets.updated_at');
            })
            ->count();

        $pendientesAprobacion = TicketSolicitudResolucion::where('id_usuario', $usuario->id)
            ->where('es_solicitante', false)
            ->whereNull('aprobado_en')
            ->count();

        return $noLeidos + $pendientesAprobacion;
    }
}
