@extends('layouts.app')

@section('title', 'Licencia — ' . ($licencia->usuario->nombre_completo ?? ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('licencias.index') }}">Licencias</a></li>
    <li class="breadcrumb-item active">{{ $licencia->usuario->nombre_completo ?? 'Licencia' }}</li>
@endsection

@push('styles')
<style>
    .stepper { display:flex; align-items:flex-start; gap:0; margin-bottom:1.5rem; }
    .stepper-step { flex:1; text-align:center; position:relative; }
    .stepper-step::before {
        content:''; position:absolute; top:18px; left:50%; right:-50%;
        height:3px; background:#dee2e6; z-index:0;
    }
    .stepper-step:last-child::before { display:none; }
    .stepper-circle {
        width:38px; height:38px; border-radius:50%; background:#e9ecef; color:#6c757d;
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.9rem; margin:0 auto .4rem;
        position:relative; z-index:1; border:3px solid #dee2e6;
    }
    .stepper-step.done     .stepper-circle { background:var(--celeste); color:#fff; border-color:var(--celeste); }
    .stepper-step.active   .stepper-circle { background:var(--azul);    color:#fff; border-color:var(--azul); }
    .stepper-step.rejected .stepper-circle { background:#dc3545;        color:#fff; border-color:#dc3545; }
    .stepper-label { font-size:.78rem; font-weight:600; color:#6c757d; }
    .stepper-step.active   .stepper-label { color:var(--azul); }
    .stepper-step.rejected .stepper-label { color:#dc3545; }
    .stepper-step.done     .stepper-label { color:var(--celeste); }
</style>
@endpush

@section('content')

@php
    $estado     = $licencia->estado;
    $badgeColor = ['pendiente'=>'warning','aprobada'=>'success','rechazada'=>'danger'][$estado] ?? 'secondary';
    $textColor  = $estado === 'pendiente' ? 'dark' : 'white';
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 small mb-3" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--azul)">
            <i class="bi bi-calendar-check me-1"></i> Licencia
            <span class="badge bg-{{ $badgeColor }} text-{{ $textColor }} ms-1">{{ ucfirst($estado) }}</span>
        </h5>
        <div class="small text-muted">{{ $licencia->usuario->nombre_completo ?? '—' }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($estado === 'pendiente')
            <a href="{{ route('licencias.edit', $licencia) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            @if($puedeAutorizar ?? false)
                <form method="POST" action="{{ route('licencias.aprobar', $licencia) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success"
                            onclick="return confirm('¿Aprobar esta licencia?')">
                        <i class="bi bi-check-circle me-1"></i> Aprobar
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-danger"
                        data-bs-toggle="modal" data-bs-target="#modalRechazar">
                    <i class="bi bi-x-circle me-1"></i> Rechazar
                </button>
            @else
                <span class="badge bg-secondary align-self-center py-2 px-3">
                    <i class="bi bi-lock me-1"></i> Sin permiso para autorizar
                </span>
            @endif
        @endif
        <a href="{{ route('licencias.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

{{-- Stepper --}}
<div class="card mb-3">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-diagram-2 me-1"></i> Estado del trámite
    </div>
    <div class="card-body py-3">
        <div class="stepper">
            <div class="stepper-step done">
                <div class="stepper-circle"><i class="bi bi-check-lg"></i></div>
                <div class="stepper-label">Registrada</div>
            </div>
            <div class="stepper-step {{ $estado === 'pendiente' ? 'active' : 'done' }}">
                <div class="stepper-circle">
                    @if($estado !== 'pendiente') <i class="bi bi-check-lg"></i> @else 2 @endif
                </div>
                <div class="stepper-label">Pendiente</div>
            </div>
            <div class="stepper-step {{ $estado === 'aprobada' ? 'done' : ($estado === 'rechazada' ? 'rejected' : '') }}">
                <div class="stepper-circle">
                    @if($estado === 'aprobada') <i class="bi bi-check-lg"></i>
                    @elseif($estado === 'rechazada') <i class="bi bi-x-lg"></i>
                    @else 3
                    @endif
                </div>
                <div class="stepper-label">{{ $estado === 'rechazada' ? 'Rechazada' : 'Aprobada' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Detalle + resolución --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos de la licencia
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Usuario</dt>
                    <dd class="col-sm-8">{{ $licencia->usuario->nombre_completo ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Tipo</dt>
                    <dd class="col-sm-8">
                        {{ $licencia->tipoLicencia->nombre ?? '—' }}
                        @if($licencia->tipoLicencia?->requiere_documentacion)
                            <span class="badge bg-warning text-dark ms-1">requiere doc.</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted">Designación</dt>
                    <dd class="col-sm-8">
                        {{ $licencia->designacion?->cargo->nombre ?? '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted">Período</dt>
                    <dd class="col-sm-8">
                        {{ $licencia->fecha_inicio?->format('d/m/Y') ?? '—' }}
                        →
                        {{ $licencia->fecha_fin?->format('d/m/Y') ?? '∞' }}
                    </dd>

                    <dt class="col-sm-4 text-muted">Días computados</dt>
                    <dd class="col-sm-8">
                        {{ $licencia->dias_computados ?? '—' }}
                        @if($licencia->tipoLicencia)
                            <span class="text-muted ms-1">({{ $licencia->tipoLicencia->esDiasCorridos() ? 'corridos' : 'hábiles' }})</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted">Motivo</dt>
                    <dd class="col-sm-8">{{ $licencia->motivo ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Documentación</dt>
                    <dd class="col-sm-8">
                        @if($licencia->documentacion)
                            <a href="{{ Storage::url($licencia->documentacion) }}" target="_blank" class="small">
                                <i class="bi bi-paperclip me-1"></i> Ver archivo
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted">Registrado por</dt>
                    <dd class="col-sm-8">{{ $licencia->registradoPor->nombre_completo ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-clipboard-check me-1"></i> Resolución
            </div>
            <div class="card-body">
                @if($estado === 'pendiente')
                    <p class="text-muted small mb-0">
                        <i class="bi bi-hourglass-split me-1"></i>
                        Pendiente de resolución.
                    </p>
                @else
                    <dl class="row small mb-0">
                        <dt class="col-sm-5 text-muted">Resuelto por</dt>
                        <dd class="col-sm-7">{{ $licencia->aprobadoPor->nombre_completo ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted">Fecha</dt>
                        <dd class="col-sm-7">
                            {{ $licencia->fecha_aprobacion?->format('d/m/Y H:i') ?? '—' }}
                        </dd>

                        @if($estado === 'rechazada')
                            <dt class="col-sm-5 text-muted">Motivo rechazo</dt>
                            <dd class="col-sm-7">{{ $licencia->observaciones_aprobacion ?? '—' }}</dd>
                        @endif
                    </dl>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal Rechazar --}}
@if($estado === 'pendiente' && ($puedeAutorizar ?? false))
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('licencias.rechazar', $licencia) }}">
                @csrf
                <div class="modal-header" style="background:var(--azul);color:#fff">
                    <h6 class="modal-title"><i class="bi bi-x-circle me-1"></i> Rechazar licencia</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold small">
                        Motivo del rechazo <span class="text-danger">*</span>
                    </label>
                    <textarea name="observaciones" rows="4"
                              class="form-control form-control-sm" required
                              placeholder="Indique el motivo del rechazo…">{{ old('observaciones') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Confirmar rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
