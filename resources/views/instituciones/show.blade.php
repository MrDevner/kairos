@extends('layouts.app')
@section('title', $institucion->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('instituciones.index') }}">Instituciones</a></li>
    <li class="breadcrumb-item active">{{ $institucion->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-building me-1"></i> {{ $institucion->nombre }}
        @if($institucion->sigla)
            <small class="badge bg-secondary fw-normal ms-1">{{ $institucion->sigla }}</small>
        @endif
    </h5>
    <div>
        <a href="{{ route('instituciones.edit', $institucion) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('instituciones.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Columna izquierda --}}
    <div class="col-md-5">

        {{-- Datos generales --}}
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos generales
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Sigla</dt>
                    <dd class="col-7">{{ $institucion->sigla ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Institución padre</dt>
                    <dd class="col-7">
                        @if($institucion->padre)
                            <a href="{{ route('instituciones.show', $institucion->padre) }}" class="text-decoration-none">
                                {{ $institucion->padre->nombre }}
                            </a>
                        @else
                            <span class="text-muted">— (raíz)</span>
                        @endif
                    </dd>

                    @if($institucion->descripcion)
                        <dt class="col-5 text-muted">Descripción</dt>
                        <dd class="col-7">{{ $institucion->descripcion }}</dd>
                    @endif

                    @if($institucion->direccion)
                        <dt class="col-5 text-muted">Dirección</dt>
                        <dd class="col-7">{{ $institucion->direccion }}</dd>
                    @endif

                    @if($institucion->telefono)
                        <dt class="col-5 text-muted">Teléfono</dt>
                        <dd class="col-7">{{ $institucion->telefono }}</dd>
                    @endif

                    @if($institucion->email)
                        <dt class="col-5 text-muted">Email</dt>
                        <dd class="col-7 text-break">
                            <a href="mailto:{{ $institucion->email }}" class="text-decoration-none">
                                {{ $institucion->email }}
                            </a>
                        </dd>
                    @endif

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $institucion->activa ? 'success' : 'secondary' }}">
                            {{ $institucion->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Configuración --}}
        <div class="card mt-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-sliders me-1"></i> Configuración operativa
            </div>
            <div class="card-body">
                @php $cfg = $institucion->configuracion; @endphp
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted">Umbral mín. de jornada</dt>
                    <dd class="col-5">{{ $cfg['umbral_jornada_minima'] }} min</dd>

                    <dt class="col-7 text-muted">Banco de horas por</dt>
                    <dd class="col-5">{{ ucfirst($cfg['banco_horas_por']) }}</dd>

                    <dt class="col-7 text-muted">Avisos de usuario</dt>
                    <dd class="col-5">
                        @if($cfg['permite_avisos_usuario'])
                            <span class="badge bg-success">Habilitado</span>
                        @else
                            <span class="badge bg-secondary">Deshabilitado</span>
                        @endif
                    </dd>

                    <dt class="col-7 text-muted">Horas extra</dt>
                    <dd class="col-5">
                        @if($cfg['horas_extra_autorizadas'])
                            <span class="badge bg-success">Autorizadas</span>
                        @else
                            <span class="badge bg-secondary">No autorizadas</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Columna derecha --}}
    <div class="col-md-7">

        {{-- Sub-instituciones --}}
        <div class="card">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-diagram-3 me-1"></i> Sub-instituciones
                <span class="badge bg-light text-dark ms-auto">{{ $institucion->hijas->count() }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($institucion->hijas as $h)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <a href="{{ route('instituciones.show', $h) }}" class="text-decoration-none fw-semibold">
                            {{ $h->nombre }}
                        </a>
                        <span class="badge bg-{{ $h->activa ? 'success' : 'secondary' }}">
                            {{ $h->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small py-3 text-center">
                        <i class="bi bi-inbox d-block mb-1 fs-5"></i>
                        Sin sub-instituciones
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Dependencias --}}
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-diagram-2 me-1"></i> Dependencias
                <span class="badge bg-light text-dark ms-auto">{{ $institucion->dependencias->count() }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($institucion->dependencias->take(15) as $dep)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                        <a href="{{ route('dependencias.show', $dep) }}" class="text-decoration-none">
                            {{ $dep->nombre }}
                            @if($dep->sigla)
                                <span class="text-muted">({{ $dep->sigla }})</span>
                            @endif
                        </a>
                        <span class="badge bg-{{ $dep->activa ? 'success' : 'secondary' }}">
                            {{ $dep->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small py-3 text-center">
                        <i class="bi bi-inbox d-block mb-1 fs-5"></i>
                        Sin dependencias
                    </li>
                @endforelse
                @if($institucion->dependencias->count() > 15)
                    <li class="list-group-item text-muted small text-center py-2">
                        … y {{ $institucion->dependencias->count() - 15 }} más
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection
