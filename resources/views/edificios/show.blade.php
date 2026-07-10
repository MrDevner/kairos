@extends('layouts.app')

@section('title', $edificio->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('edificios.index') }}">Edificios / Complejos</a></li>
    <li class="breadcrumb-item active">{{ $edificio->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--azul)">
            <i class="bi bi-building me-1"></i> {{ $edificio->nombre }}
            @if($edificio->activo)
                <span class="badge bg-success ms-1">Activo</span>
            @else
                <span class="badge bg-secondary ms-1">Inactivo</span>
            @endif
        </h5>
        <div class="small text-muted">{{ $edificio->institucion->nombre ?? '—' }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('edificios.edit', $edificio) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('edificios.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

@if($edificio->descripcion)
    <div class="card mb-3">
        <div class="card-body small">{{ $edificio->descripcion }}</div>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-door-open-fill me-2"></i> Oficinas / Aulas
        <a href="{{ route('oficinas.create', ['id_edificio' => $edificio->id]) }}" class="btn btn-sm btn-light ms-auto py-0">
            <i class="bi bi-plus-lg me-1"></i> Nueva oficina
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($edificio->oficinas as $of)
                        <tr>
                            <td>{{ $of->nombre }}</td>
                            <td class="text-center">
                                @if($of->activo)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-secondary">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('oficinas.show', $of) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('oficinas.edit', $of) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Sin oficinas/aulas cargadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
