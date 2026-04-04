@extends('layouts.app')

@section('title', 'Aviso #' . $aviso->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('avisos.index') }}">Avisos del personal</a></li>
    <li class="breadcrumb-item active">#{{ $aviso->id }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-megaphone-fill me-1"></i> Aviso #{{ $aviso->id }}
    </h5>
    <div class="d-flex gap-2">
        @can('editar', $aviso)
            <a href="{{ route('avisos.edit', $aviso) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <form method="POST" action="{{ route('avisos.destroy', $aviso) }}"
                  onsubmit="return confirm('¿Eliminar este aviso?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
        @endcan
        <a href="{{ route('avisos.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card" style="max-width:600px">
    {{-- Header con badge de tipo --}}
    <div class="card-header d-flex align-items-center gap-2" style="background:var(--azul);color:#fff">
        @if($aviso->tipo === 'ausencia')
            <span class="badge bg-warning text-dark fs-6">
                <i class="bi bi-calendar-x me-1"></i> Ausencia
            </span>
        @else
            <span class="badge bg-info text-dark fs-6">
                <i class="bi bi-clock me-1"></i> Tardanza
            </span>
        @endif
        <span class="ms-1">{{ $aviso->fecha_evento?->format('d/m/Y') }}</span>
    </div>

    <div class="card-body">
        <dl class="row row-cols-1 row-cols-md-2 g-0 mb-0 small">

            <div class="col py-2 border-bottom">
                <dt class="text-muted fw-semibold mb-0">Empleado</dt>
                <dd class="mb-0">
                    <a href="{{ route('usuarios.show', $aviso->usuario) }}" class="text-decoration-none">
                        {{ $aviso->usuario?->nombre_completo ?? '—' }}
                    </a>
                </dd>
            </div>

            <div class="col py-2 border-bottom">
                <dt class="text-muted fw-semibold mb-0">Institución</dt>
                <dd class="mb-0">{{ $aviso->institucion?->nombre ?? '—' }}</dd>
            </div>

            <div class="col py-2 border-bottom">
                <dt class="text-muted fw-semibold mb-0">Dependencia</dt>
                <dd class="mb-0">{{ $aviso->designacion?->dependencia?->nombre ?? '—' }}</dd>
            </div>

            <div class="col py-2 border-bottom">
                <dt class="text-muted fw-semibold mb-0">Fecha del aviso <small class="fw-normal">(notificación)</small></dt>
                <dd class="mb-0 font-monospace">{{ $aviso->fecha_aviso?->format('d/m/Y') }}</dd>
            </div>

            <div class="col py-2 border-bottom">
                <dt class="text-muted fw-semibold mb-0">Fecha del evento <small class="fw-normal">(ocurrencia)</small></dt>
                <dd class="mb-0 font-monospace">
                    {{ $aviso->fecha_evento?->format('d/m/Y') }}
                    @if($aviso->fecha_aviso && $aviso->fecha_evento && $aviso->fecha_aviso->lt($aviso->fecha_evento))
                        <span class="badge bg-info text-dark ms-1 small">aviso anticipado</span>
                    @endif
                </dd>
            </div>

            @if($aviso->tipo === 'ausencia')
                <div class="col py-2 border-bottom">
                    <dt class="text-muted fw-semibold mb-0">Motivo (tipo de licencia)</dt>
                    <dd class="mb-0">{{ $aviso->tipoLicencia?->nombre ?? '—' }}</dd>
                </div>
            @else
                <div class="col py-2 border-bottom">
                    <dt class="text-muted fw-semibold mb-0">Hora estimada de llegada</dt>
                    <dd class="mb-0 font-monospace">
                        {{ $aviso->hora_estimada_llegada
                            ? \Carbon\Carbon::parse($aviso->hora_estimada_llegada)->format('H:i')
                            : '—' }}
                    </dd>
                </div>
            @endif

            @if($aviso->motivo)
                <div class="col col-md-12 py-2 border-bottom">
                    <dt class="text-muted fw-semibold mb-0">Observaciones</dt>
                    <dd class="mb-0">{{ $aviso->motivo }}</dd>
                </div>
            @endif

            <div class="col py-2 border-bottom">
                <dt class="text-muted fw-semibold mb-0">Registrado por</dt>
                <dd class="mb-0">{{ $aviso->registradoPor?->nombre_completo ?? '—' }}</dd>
            </div>

            <div class="col py-2">
                <dt class="text-muted fw-semibold mb-0">Fecha de registro</dt>
                <dd class="mb-0 font-monospace">{{ $aviso->created_at?->format('d/m/Y H:i') }}</dd>
            </div>

        </dl>
    </div>
</div>
@endsection
