@extends('layouts.app')

@section('title', 'Licencia — ' . ($licencia->usuario->nombre_completo ?? ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('licencias.index') }}">Licencias</a></li>
    <li class="breadcrumb-item active">{{ $licencia->usuario->nombre_completo ?? 'Licencia' }}</li>
@endsection

@push('styles')
<style>
    .stepper { display: flex; align-items: flex-start; gap: 0; margin-bottom: 1.5rem; }
    .stepper-step { flex: 1; text-align: center; position: relative; }
    .stepper-step::before {
        content: '';
        position: absolute;
        top: 18px;
        left: 50%;
        right: -50%;
        height: 3px;
        background: #dee2e6;
        z-index: 0;
    }
    .stepper-step:last-child::before { display: none; }
    .stepper-circle {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .9rem;
        margin: 0 auto .4rem;
        position: relative;
        z-index: 1;
        border: 3px solid #dee2e6;
    }
    .stepper-step.done .stepper-circle   { background: var(--celeste); color: #fff; border-color: var(--celeste); }
    .stepper-step.active .stepper-circle { background: var(--azul); color: #fff; border-color: var(--azul); }
    .stepper-step.rejected .stepper-circle { background: #dc3545; color: #fff; border-color: #dc3545; }
    .stepper-label { font-size: .78rem; font-weight: 600; color: #6c757d; }
    .stepper-step.active .stepper-label { color: var(--azul); }
    .stepper-step.rejected .stepper-label { color: #dc3545; }
    .stepper-step.done .stepper-label { color: var(--celeste); }
</style>
@endpush

@section('content')

@php
    $estado = $licencia->estado;
    $estadoBadge = ['pendiente'=>'warning','aprobada'=>'success','rechazada'=>'danger'][$estado] ?? 'secondary';
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--azul)">
            <i class="bi bi-calendar-check me-1"></i> Licencia
            <span class="badge bg-{{ $estadoBadge }} ms-1">{{ ucfirst($estado) }}</span>
        </h5>
        <div class="small text-muted">{{ $licencia->usuario->nombre_completo ?? '—' }}</div>
    </div>
    <div class="d-flex gap-2">
        @if($estado === 'pendiente')
            <form method="POST" action="{{ route('licencias.aprobar', $licencia) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Aprobar esta licencia?')">
                    <i class="bi bi-check-circle me-1"></i> Aprobar
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                <i class="bi bi-x-circle me-1"></i> Rechazar
            </button>
        @endif
        <a href="{{ route('licencias.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

{{-- Stepper workflow --}}
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
            <div class="stepper-step {{ in_array($estado, ['pendiente','aprobada','rechazada']) ? ($estado === 'pendiente' ? 'active' : 'done') : '' }}">
                <div class="stepper-circle">
                    @if(in_array($estado, ['aprobada','rechazada']))
                        <i class="bi bi-check-lg"></i>
                    @else
                        2
                    @endif
                </div>
                <div class="stepper-label">Pendiente</div>
            </div>
            <div class="stepper-step {{ $estado === 'aprobada' ? 'done' : ($estado === 'rechazada' ? 'rejected' : '') }}">
                <div class="stepper-circle">
                    @if($estado === 'aprobada')
                        <i class="bi bi-check-lg"></i>
                    @elseif($estado === 'rechazada')
                        <i class="bi bi-x-lg"></i>
                    @else
                        3
                    @endif
                </div>
                <div class="stepper-label">
                    @if($estado === 'rechazada') Rechazada
                    @else Aprobada
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Datos --}}
<div class="card">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-info-circle me-1"></i> Detalle
    </div>
    <div class="card-body">
        <dl class="row small mb-0">
            <dt class="col-md-3 text-muted">Usuario</dt>
            <dd class="col-md-9">{{ $licencia->usuario->nombre_completo ?? '—' }}</dd>

            <dt class="col-md-3 text-muted">Tipo de licencia</dt>
            <dd class="col-md-9">{{ $licencia->tipoLicencia->nombre ?? '—' }}</dd>

            <dt class="col-md-3 text-muted">Período</dt>
            <dd class="col-md-9">
                {{ $licencia->fecha_inicio ? \Carbon\Carbon::parse($licencia->fecha_inicio)->format('d/m/Y') : '—' }}
                — {{ $licencia->fecha_fin ? \Carbon\Carbon::parse($licencia->fecha_fin)->format('d/m/Y') : '—' }}
            </dd>

            <dt class="col-md-3 text-muted">Días solicitados</dt>
            <dd class="col-md-9">{{ $licencia->dias_solicitados ?? '—' }}</dd>

            <dt class="col-md-3 text-muted">Motivo</dt>
            <dd class="col-md-9">{{ $licencia->motivo ?? '—' }}</dd>

            <dt class="col-md-3 text-muted">Observaciones</dt>
            <dd class="col-md-9">{{ $licencia->observaciones ?? '—' }}</dd>

            <dt class="col-md-3 text-muted">Aprobada por</dt>
            <dd class="col-md-9">{{ $licencia->aprobadaPor->nombre_completo ?? '—' }}</dd>

            <dt class="col-md-3 text-muted">Fecha resolución</dt>
            <dd class="col-md-9">{{ $licencia->fecha_resolucion ? \Carbon\Carbon::parse($licencia->fecha_resolucion)->format('d/m/Y') : '—' }}</dd>
        </dl>
    </div>
</div>

{{-- Modal Rechazar --}}
@if($estado === 'pendiente')
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
                    <label class="form-label fw-semibold small">Motivo del rechazo <span class="text-danger">*</span></label>
                    <textarea name="observaciones" rows="4" class="form-control form-control-sm" required
                              placeholder="Indique el motivo del rechazo…">{{ old('observaciones') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
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
