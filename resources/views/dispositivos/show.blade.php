@extends('layouts.app')

@section('title', $dispositivo->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dispositivos.index') }}">Dispositivos</a></li>
    <li class="breadcrumb-item active">{{ $dispositivo->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-hdd-network-fill me-1"></i> {{ $dispositivo->nombre }}
    </h5>
    <div>
        <a href="{{ route('dispositivos.edit', $dispositivo) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

@php
    $tipoMap  = ['biometrico' => ['Biométrico','secondary'], 'web' => ['Web','info'], 'otro' => ['Otro','light']];
    $modoMap  = ['directo_bd' => 'Directo a BD', 'importacion' => 'Importación', 'web' => 'Web'];
    [$tipoLabel, $tipoColor] = $tipoMap[$dispositivo->tipo] ?? [$dispositivo->tipo, 'light'];
@endphp

<div class="row g-3" style="max-width:820px">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Información general
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Institución</dt>
                    <dd class="col-7">
                        @if($dispositivo->institucion)
                            <a href="{{ route('instituciones.show', $dispositivo->institucion) }}" class="text-decoration-none">
                                {{ $dispositivo->institucion->nombre }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Ubicación</dt>
                    <dd class="col-7">{{ $dispositivo->ubicacion }}</dd>

                    <dt class="col-5 text-muted">Tipo</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $tipoColor }} text-dark">{{ $tipoLabel }}</span>
                    </dd>

                    <dt class="col-5 text-muted">Modo conexión</dt>
                    <dd class="col-7">{{ $modoMap[$dispositivo->modo_conexion] ?? $dispositivo->modo_conexion }}</dd>

                    <dt class="col-5 text-muted">Dirección IP</dt>
                    <dd class="col-7 font-monospace">{{ $dispositivo->ip_address ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $dispositivo->activo ? 'success' : 'secondary' }}">
                            {{ $dispositivo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-sliders me-1"></i> Configuración
            </div>
            <div class="card-body">
                @if(!empty($dispositivo->configuracion))
                    <pre class="mb-0 small">{{ json_encode($dispositivo->configuracion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <p class="text-muted small mb-0 text-center py-3">
                        <i class="bi bi-sliders d-block mb-1 fs-5"></i>
                        Sin configuración adicional.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
