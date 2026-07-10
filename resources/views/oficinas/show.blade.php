@extends('layouts.app')

@section('title', $oficina->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('oficinas.index') }}">Oficinas / Aulas</a></li>
    <li class="breadcrumb-item active">{{ $oficina->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--azul)">
            <i class="bi bi-door-open-fill me-1"></i> {{ $oficina->nombre }}
            @if($oficina->activo)
                <span class="badge bg-success ms-1">Activa</span>
            @else
                <span class="badge bg-secondary ms-1">Inactiva</span>
            @endif
        </h5>
        <div class="small text-muted">
            <a href="{{ route('edificios.show', $oficina->edificio) }}" class="text-decoration-none">
                {{ $oficina->edificio->nombre ?? '—' }}
            </a>
            — {{ $oficina->edificio->institucion->nombre ?? '—' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('oficinas.edit', $oficina) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('oficinas.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

@if($oficina->descripcion)
    <div class="card">
        <div class="card-body small">{{ $oficina->descripcion }}</div>
    </div>
@endif
@endsection
