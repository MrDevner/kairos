@extends('layouts.app')

@section('title', $ticket->titulo)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">#{{ $ticket->id }}</li>
@endsection

@section('content')

@php
    $estadoColor = ['abierto'=>'danger','en_proceso'=>'warning','resuelto'=>'info','cerrado'=>'success'][$ticket->estado] ?? 'secondary';
    $prioridadColor = ['baja'=>'secondary','media'=>'info','alta'=>'warning','urgente'=>'danger'][$ticket->prioridad] ?? 'secondary';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-life-preserver me-1"></i> {{ $ticket->titulo }}
        <span class="badge bg-{{ $estadoColor }}">{{ ucfirst(str_replace('_',' ',$ticket->estado)) }}</span>
        <span class="badge bg-{{ $prioridadColor }}">{{ ucfirst($ticket->prioridad) }}</span>
    </h5>
    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Descripción --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-card-text me-1"></i> Descripción
            </div>
            <div class="card-body">
                <p class="mb-2" style="white-space:pre-wrap">{{ $ticket->descripcion }}</p>
                @if($ticket->adjuntos->isNotEmpty())
                <div class="d-flex flex-wrap gap-1">
                    @foreach($ticket->adjuntos as $adj)
                        <a href="{{ route('tickets.adjuntos.descargar', $adj) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-paperclip"></i> {{ Str::limit($adj->nombre_original, 25) }}
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Mensajería --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-chat-dots me-1"></i> Mensajes
                <span class="badge bg-light text-dark ms-1">{{ $ticket->mensajes->count() }}</span>
            </div>
            <div class="card-body">
                @forelse($ticket->mensajes as $mensaje)
                    <div class="mb-3 pb-3 border-bottom small">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">{{ $mensaje->usuario?->nombre_completo }}</span>
                            <span class="text-muted">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div style="white-space:pre-wrap">{{ $mensaje->mensaje }}</div>
                        @if($mensaje->adjuntos->isNotEmpty())
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($mensaje->adjuntos as $adj)
                                <a href="{{ route('tickets.mensajes.adjuntos.descargar', $adj) }}" class="btn btn-sm btn-outline-secondary py-0">
                                    <i class="bi bi-paperclip"></i> {{ Str::limit($adj->nombre_original, 25) }}
                                </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0">Todavía no hay mensajes.</p>
                @endforelse

                @if($ticket->estado !== 'cerrado')
                <form method="POST" action="{{ route('tickets.mensajes.store', $ticket) }}" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <textarea name="mensaje" class="form-control form-control-sm mb-2" rows="3" required maxlength="5000" placeholder="Escribir un mensaje…"></textarea>
                    <input type="file" name="adjuntos[]" multiple class="form-control form-control-sm mb-2">
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                        <i class="bi bi-send me-1"></i> Enviar
                    </button>
                </form>
                @else
                <p class="text-muted small mb-0"><i class="bi bi-lock-fill me-1"></i> El ticket está cerrado.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Información --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff"><i class="bi bi-info-circle me-1"></i> Información</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Categoría</dt>
                    <dd class="col-7">{{ $ticket->categoria }}</dd>
                    <dt class="col-5 text-muted">Creador</dt>
                    <dd class="col-7">{{ $ticket->creador?->nombre_completo }}</dd>
                    @if($ticket->id_abierto_por !== $ticket->id_creador)
                    <dt class="col-5 text-muted">Abierto por</dt>
                    <dd class="col-7">{{ $ticket->abiertoPor?->nombre_completo }}</dd>
                    @endif
                    <dt class="col-5 text-muted">Asignado</dt>
                    <dd class="col-7">{{ $ticket->asignadoA?->nombre_completo ?? '— Sin asignar' }}</dd>
                    <dt class="col-5 text-muted">Fecha límite</dt>
                    <dd class="col-7">{{ $ticket->fecha_limite?->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Creado</dt>
                    <dd class="col-7">{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
                    @if($ticket->fecha_cierre)
                    <dt class="col-5 text-muted">Cierre</dt>
                    <dd class="col-7">{{ $ticket->fecha_cierre->format('d/m/Y H:i') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Panel de soporte --}}
        @if($esSoporte)
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff"><i class="bi bi-gear me-1"></i> Gestión (soporte)</div>
            <div class="card-body">
                @if(is_null($ticket->id_asignado_a) && $ticket->estado === 'abierto')
                <form method="POST" action="{{ route('tickets.tomar', $ticket) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-sm w-100" style="background:var(--azul);color:#fff">
                        <i class="bi bi-hand-index-thumb me-1"></i> Tomar ticket
                    </button>
                </form>
                @endif

                <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                    @csrf @method('PUT')

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            @foreach(\App\Models\Ticket::ESTADOS as $e)
                                <option value="{{ $e }}" @selected($ticket->estado === $e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Prioridad</label>
                        <select name="prioridad" class="form-select form-select-sm">
                            @foreach(\App\Models\Ticket::PRIORIDADES as $p)
                                <option value="{{ $p }}" @selected($ticket->prioridad === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Fecha límite</label>
                        <input type="date" name="fecha_limite" value="{{ $ticket->fecha_limite?->format('Y-m-d') }}" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Asignado a</label>
                        <select name="id_asignado_a" class="form-select form-select-sm">
                            <option value="">— Sin asignar —</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}" @selected($ticket->id_asignado_a === $u->id)>{{ $u->apellidos }}, {{ $u->nombres }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Motivo (si reasigna o cambia creador)</label>
                        <input type="text" name="motivo" class="form-control form-control-sm" minlength="5" maxlength="1000">
                    </div>
                    <button type="submit" class="btn btn-sm w-100" style="background:var(--azul);color:#fff">
                        <i class="bi bi-check-lg me-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Cambiar categoría --}}
        @if($esSoporte || $ticket->id_asignado_a === auth()->id())
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff"><i class="bi bi-tag me-1"></i> Cambiar categoría</div>
            <div class="card-body">
                <form method="POST" action="{{ route('tickets.categoria', $ticket) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="categoria" class="form-select form-select-sm">
                            @foreach($categorias as $c)
                                <option value="{{ $c }}" @selected($ticket->categoria === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="motivo" class="form-control form-control-sm" placeholder="Motivo (mínimo 10 caracteres)" minlength="10" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Actualizar categoría</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Resolución colaborativa --}}
        @if($ticket->estado !== 'cerrado')
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff"><i class="bi bi-check2-circle me-1"></i> Resolución</div>
            <div class="card-body">
                @if($hayResolucionPendiente)
                    <p class="small mb-2">Hay una solicitud de cierre pendiente de aprobación.</p>
                    @if($miSolicitud && is_null($miSolicitud->aprobado_en))
                        <form method="POST" action="{{ route('tickets.resolucion.aprobar', $ticket) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-sm w-100" style="background:var(--azul);color:#fff">
                                <i class="bi bi-check-lg me-1"></i> Aprobar cierre
                            </button>
                        </form>
                    @endif
                    @if($esSoporte || ($miSolicitud && $miSolicitud->es_solicitante))
                        <form method="POST" action="{{ route('tickets.resolucion.cancelar', $ticket) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Cancelar solicitud</button>
                        </form>
                    @endif
                @else
                    <form method="POST" action="{{ route('tickets.resolucion.solicitar', $ticket) }}">
                        @csrf
                        @if($esSoporte)
                        <div class="mb-2">
                            <select name="estado_propuesto" class="form-select form-select-sm">
                                <option value="resuelto">Resuelto</option>
                                <option value="cerrado">Cerrado</option>
                            </select>
                        </div>
                        @endif
                        <button type="submit" class="btn btn-sm w-100" style="background:var(--azul);color:#fff">
                            <i class="bi bi-flag me-1"></i> Solicitar cierre
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
