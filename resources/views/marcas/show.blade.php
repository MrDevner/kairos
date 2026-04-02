@extends('layouts.app')

@section('title', 'Marcas — ' . ($marca->usuario->nombre_completo ?? '') . ' — ' . ($marca->fecha ?? ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas</a></li>
    <li class="breadcrumb-item">{{ $marca->usuario->nombre_completo ?? 'Usuario' }}</li>
    <li class="breadcrumb-item active">{{ $marca->fecha ? \Carbon\Carbon::parse($marca->fecha)->format('d/m/Y') : '—' }}</li>
@endsection

@push('styles')
<style>
    .timeline { position: relative; padding-left: 2rem; }
    .timeline::before {
        content: '';
        position: absolute;
        left: .75rem;
        top: 0; bottom: 0;
        width: 2px;
        background: var(--celeste);
    }
    .timeline-item { position: relative; margin-bottom: 1.2rem; }
    .timeline-dot {
        position: absolute;
        left: -1.75rem;
        top: .15rem;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: var(--azul);
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px var(--celeste);
    }
    .timeline-content {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: .5rem .75rem;
        font-size: .83rem;
    }
</style>
@endpush

@section('content')
{{-- Header usuario --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div class="d-flex align-items-center gap-3">
        <div style="width:54px;height:54px;border-radius:50%;background:var(--azul);color:#fff;
                    display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem">
            {{ strtoupper(substr($marca->usuario->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($marca->usuario->apellidos ?? '', 0, 1)) }}
        </div>
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--azul)">{{ $marca->usuario->nombre_completo ?? '—' }}</h5>
            <div class="small text-muted">
                {{ $marca->designacion->cargo->nombre ?? '—' }} —
                {{ $marca->fecha ? \Carbon\Carbon::parse($marca->fecha)->format('d/m/Y') : '—' }}
            </div>
        </div>
    </div>
    <a href="{{ route('marcas.index', ['fecha' => $marca->fecha]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row g-3">
    {{-- Columna izquierda: timeline de marcas originales --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-clock me-1"></i> Registros del dispositivo
            </div>
            <div class="card-body">
                @if($originales->isEmpty())
                    <p class="text-muted text-center small py-2 mb-0">
                        <i class="bi bi-clock-history fs-4 d-block mb-1"></i>
                        Sin registros de dispositivo para esta fecha.
                    </p>
                @else
                    <div class="timeline">
                        @foreach($originales->sortBy('fecha_hora') as $reg)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($reg->fecha_hora)->format('H:i:s') }}
                                            </span>
                                            <div class="text-muted small">{{ $reg->dispositivo->nombre ?? 'Dispositivo desconocido' }}</div>
                                        </div>
                                        <span class="badge {{ $reg->tipo_captura === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($reg->tipo_captura) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Columna derecha: marcas calculadas --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-calculator me-1"></i> Marca calculada
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Hora entrada</dt>
                    <dd class="col-7">
                        {{ $marca->hora_entrada ? \Carbon\Carbon::parse($marca->hora_entrada)->format('H:i') : '—' }}
                    </dd>

                    <dt class="col-5 text-muted">Hora salida</dt>
                    <dd class="col-7">
                        {{ $marca->hora_salida ? \Carbon\Carbon::parse($marca->hora_salida)->format('H:i') : '—' }}
                    </dd>

                    <dt class="col-5 text-muted">Min. trabajados</dt>
                    <dd class="col-7">{{ $marca->minutos_trabajados ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Min. obligatorios</dt>
                    <dd class="col-7">{{ $marca->minutos_obligatorios ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Diferencia</dt>
                    <dd class="col-7">
                        @php
                            $diff = ($marca->minutos_trabajados ?? 0) - ($marca->minutos_obligatorios ?? 0);
                        @endphp
                        <span class="{{ $diff >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                            {{ $diff >= 0 ? '+' : '' }}{{ $diff }} min
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">Tipo</dt>
                    <dd class="col-7">
                        @if($marca->tipo)
                            <span class="badge bg-secondary">{{ ucfirst($marca->tipo) }}</span>
                        @else —
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Errores</dt>
                    <dd class="col-7">
                        @if($marca->tiene_error)
                            <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>
                                {{ $marca->error_descripcion ?? 'Error' }}
                            </span>
                        @else
                            <span class="badge bg-success">Sin errores</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
