<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAdjunto;
use App\Models\TicketCategoria;
use App\Models\TicketLectura;
use App\Models\TicketMensaje;
use App\Models\TicketMensajeAdjunto;
use App\Models\TicketSolicitudResolucion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();

        $query = Ticket::with(['creador', 'asignadoA'])->visiblesPara($usuario);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($sub) => $sub->where('titulo', 'like', "%{$q}%")->orWhere('descripcion', 'like', "%{$q}%"));
        }

        $tickets = $query->ordenParaListado()->paginate(20)->withQueryString();
        $categorias = TicketCategoria::categorias();
        $puedeVerTodos = Ticket::puedeVerTodos($usuario);

        return view('tickets.index', compact('tickets', 'categorias', 'puedeVerTodos'));
    }

    public function create(Request $request): View
    {
        abort_unless(Ticket::puedeCrear($request->user()), 403);

        $categorias = TicketCategoria::categorias();
        $esSoporte = Ticket::esSoporte($request->user());
        $usuarios = $esSoporte
            ? User::where('activo', true)->orderBy('apellidos')->orderBy('nombres')->get(['id', 'apellidos', 'nombres'])
            : collect();

        return view('tickets.create', compact('categorias', 'esSoporte', 'usuarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless(Ticket::puedeCrear($usuario), 403);

        $data = $request->validate([
            'titulo'      => ['required', 'string', 'max:200'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'categoria'   => ['required', 'string', Rule::in(TicketCategoria::categorias()->toArray())],
            'prioridad'   => ['required', Rule::in(Ticket::PRIORIDADES)],
            'id_creador'  => ['nullable', 'integer', 'exists:users,id'],
            'adjuntos'    => ['nullable', 'array'],
            'adjuntos.*'  => ['file', 'max:10240'],
        ]);

        $esSoporte = Ticket::esSoporte($usuario);
        $idCreador = ($esSoporte && $request->filled('id_creador')) ? (int) $data['id_creador'] : $usuario->id;

        $ticket = Ticket::create([
            'titulo'         => $data['titulo'],
            'descripcion'    => $data['descripcion'],
            'categoria'      => $data['categoria'],
            'prioridad'      => $data['prioridad'],
            'estado'         => 'abierto',
            'id_creador'     => $idCreador,
            'id_abierto_por' => $usuario->id,
        ]);

        foreach ($request->file('adjuntos', []) as $archivo) {
            $ruta = $archivo->store('tickets/'.$ticket->id, 'local');

            TicketAdjunto::create([
                'id_ticket'       => $ticket->id,
                'id_usuario'      => $usuario->id,
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta'            => $ruta,
                'tipo_mime'       => $archivo->getClientMimeType(),
                'tamanio'         => $archivo->getSize(),
            ]);
        }

        TicketLectura::marcarLeido($ticket, $usuario);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket creado correctamente.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $usuario = $request->user();
        abort_unless(Ticket::puedeVerTodos($usuario) || $ticket->esParticipante($usuario), 403);

        $ticket->load(['creador', 'abiertoPor', 'asignadoA', 'adjuntos', 'mensajes.usuario', 'mensajes.adjuntos']);

        TicketLectura::marcarLeido($ticket, $usuario);

        $esSoporte = Ticket::esSoporte($usuario);
        $categorias = TicketCategoria::categorias();
        $usuarios = $esSoporte
            ? User::where('activo', true)->orderBy('apellidos')->orderBy('nombres')->get(['id', 'apellidos', 'nombres'])
            : collect();

        $miSolicitud = $ticket->solicitudesResolucion()->where('id_usuario', $usuario->id)->first();
        $hayResolucionPendiente = $ticket->solicitudesResolucion()->exists();

        return view('tickets.show', compact(
            'ticket', 'esSoporte', 'categorias', 'usuarios', 'miSolicitud', 'hayResolucionPendiente'
        ));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(Ticket::esSoporte($actor), 403);

        $data = $request->validate([
            'estado'        => ['nullable', Rule::in(Ticket::ESTADOS)],
            'prioridad'     => ['nullable', Rule::in(Ticket::PRIORIDADES)],
            'fecha_limite'  => ['nullable', 'date'],
            'id_asignado_a' => ['nullable', 'integer', 'exists:users,id'],
            'id_creador'    => ['nullable', 'integer', 'exists:users,id'],
            'motivo'        => ['nullable', 'string', 'min:5', 'max:1000'],
        ]);

        $nuevoAsignado = $request->filled('id_asignado_a') ? (int) $data['id_asignado_a'] : null;
        $nuevoCreador = $request->filled('id_creador') ? (int) $data['id_creador'] : null;

        $reasignando = $request->has('id_asignado_a') && $ticket->id_asignado_a && $nuevoAsignado !== $ticket->id_asignado_a;
        $cambiandoCreador = $nuevoCreador && $nuevoCreador !== $ticket->id_creador;

        if (($reasignando || $cambiandoCreador) && ! $request->filled('motivo')) {
            return back()->withErrors(['motivo' => 'Debe indicar un motivo (mínimo 5 caracteres).'])->withInput();
        }

        $anteriorAsignado = $ticket->id_asignado_a;
        $anteriorCreador = $ticket->id_creador;

        if ($request->filled('estado')) {
            $ticket->estado = $data['estado'];
            $ticket->fecha_cierre = in_array($data['estado'], ['resuelto', 'cerrado'], true)
                ? ($ticket->fecha_cierre ?? now())
                : null;
        }
        if ($request->filled('prioridad')) {
            $ticket->prioridad = $data['prioridad'];
        }
        if ($request->has('fecha_limite')) {
            $ticket->fecha_limite = $data['fecha_limite'] ?? null;
        }
        if ($request->has('id_asignado_a')) {
            $ticket->id_asignado_a = $nuevoAsignado;
        }
        if ($nuevoCreador) {
            $ticket->id_creador = $nuevoCreador;
        }

        $ticket->save();

        if ($reasignando || $cambiandoCreador) {
            activity('tickets')
                ->causedBy($actor)
                ->performedOn($ticket)
                ->withProperties([
                    'motivo'                 => $data['motivo'],
                    'id_asignado_a_anterior' => $anteriorAsignado,
                    'id_asignado_a_nuevo'    => $ticket->id_asignado_a,
                    'id_creador_anterior'    => $anteriorCreador,
                    'id_creador_nuevo'       => $ticket->id_creador,
                ])
                ->log('Reasignación de ticket');
        }

        return back()->with('success', 'Ticket actualizado.');
    }

    public function tomar(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(Ticket::esSoporte($actor), 403);

        $ticket->update([
            'id_asignado_a' => $actor->id,
            'estado'        => $ticket->estado === 'abierto' ? 'en_proceso' : $ticket->estado,
        ]);

        return back()->with('success', 'Ticket tomado.');
    }

    public function storeMessage(Request $request, Ticket $ticket): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless(Ticket::puedeVerTodos($usuario) || $ticket->esParticipante($usuario), 403);
        abort_if($ticket->estado === 'cerrado', 422, 'El ticket está cerrado.');

        $data = $request->validate([
            'mensaje'     => ['required', 'string', 'max:5000'],
            'adjuntos'    => ['nullable', 'array'],
            'adjuntos.*'  => ['file', 'max:10240'],
        ]);

        $mensaje = TicketMensaje::create([
            'id_ticket'  => $ticket->id,
            'id_usuario' => $usuario->id,
            'mensaje'    => $data['mensaje'],
        ]);

        foreach ($request->file('adjuntos', []) as $archivo) {
            $ruta = $archivo->store('tickets/'.$ticket->id.'/mensajes', 'local');

            TicketMensajeAdjunto::create([
                'id_ticket_mensaje' => $mensaje->id,
                'nombre_original'   => $archivo->getClientOriginalName(),
                'ruta'              => $ruta,
                'tipo_mime'         => $archivo->getClientMimeType(),
                'tamanio'           => $archivo->getSize(),
            ]);
        }

        if (Ticket::esSoporte($usuario) && $ticket->estado === 'abierto') {
            $ticket->update(['estado' => 'en_proceso']);
        } else {
            $ticket->touch();
        }

        TicketLectura::marcarLeido($ticket, $usuario);

        return back()->with('success', 'Mensaje enviado.');
    }

    public function descargarAdjunto(Request $request, TicketAdjunto $adjunto): StreamedResponse
    {
        $usuario = $request->user();
        $ticket = $adjunto->ticket;
        abort_unless(Ticket::puedeVerTodos($usuario) || $ticket->esParticipante($usuario), 403);

        return Storage::disk('local')->download($adjunto->ruta, $adjunto->nombre_original);
    }

    public function descargarAdjuntoMensaje(Request $request, TicketMensajeAdjunto $adjunto): StreamedResponse
    {
        $usuario = $request->user();
        $ticket = $adjunto->mensaje->ticket;
        abort_unless(Ticket::puedeVerTodos($usuario) || $ticket->esParticipante($usuario), 403);

        return Storage::disk('local')->download($adjunto->ruta, $adjunto->nombre_original);
    }

    public function cambiarCategoria(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(Ticket::esSoporte($actor) || $ticket->id_asignado_a === $actor->id, 403);

        $data = $request->validate([
            'categoria' => ['required', 'string', Rule::in(TicketCategoria::categorias()->toArray())],
            'motivo'    => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $ticket->update([
            'categoria'                 => $data['categoria'],
            'categoria_cambio_motivo'   => $data['motivo'],
            'id_categoria_cambiada_por' => $actor->id,
        ]);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function solicitarResolucion(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(Ticket::puedeVerTodos($actor) || $ticket->esParticipante($actor), 403);
        abort_if($ticket->solicitudesResolucion()->exists(), 422, 'Ya hay una solicitud de resolución activa.');

        $data = $request->validate([
            'estado_propuesto' => ['nullable', Rule::in(['resuelto', 'cerrado'])],
        ]);

        $esSoporte = Ticket::esSoporte($actor);
        $estadoPropuesto = $esSoporte ? ($data['estado_propuesto'] ?? 'resuelto') : null;

        $participantes = $ticket->participantes();

        // Un solo participante: se cierra inmediatamente, sin esperar aprobaciones.
        if ($participantes->count() <= 1) {
            $ticket->update([
                'estado'       => $estadoPropuesto ?? 'resuelto',
                'fecha_cierre' => now(),
            ]);

            return back()->with('success', 'Ticket cerrado.');
        }

        foreach ($participantes as $participante) {
            TicketSolicitudResolucion::create([
                'id_ticket'        => $ticket->id,
                'id_usuario'       => $participante->id,
                'es_solicitante'   => $participante->id === $actor->id,
                'aprobado_en'      => $participante->id === $actor->id ? now() : null,
                'estado_propuesto' => $estadoPropuesto,
            ]);
        }

        return back()->with('success', 'Solicitud de resolución enviada. Esperando aprobación de los participantes.');
    }

    public function aprobarResolucion(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();

        $solicitud = $ticket->solicitudesResolucion()
            ->where('id_usuario', $actor->id)
            ->whereNull('aprobado_en')
            ->firstOrFail();

        $solicitud->update(['aprobado_en' => now()]);

        $pendientes = $ticket->solicitudesResolucion()->whereNull('aprobado_en')->count();

        if ($pendientes === 0) {
            $estadoPropuesto = $ticket->solicitudesResolucion()
                ->whereNotNull('estado_propuesto')
                ->latest('updated_at')
                ->value('estado_propuesto') ?? 'resuelto';

            $ticket->update([
                'estado'       => $estadoPropuesto,
                'fecha_cierre' => now(),
            ]);

            $ticket->solicitudesResolucion()->delete();
        }

        return back()->with('success', 'Aprobación registrada.');
    }

    public function cancelarResolucion(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();

        $solicitante = $ticket->solicitudesResolucion()->where('es_solicitante', true)->first();
        abort_unless(Ticket::esSoporte($actor) || $solicitante?->id_usuario === $actor->id, 403);

        $ticket->solicitudesResolucion()->delete();

        return back()->with('success', 'Solicitud de resolución cancelada.');
    }
}
