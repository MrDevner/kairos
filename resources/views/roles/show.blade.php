@extends('layouts.app')

@section('title', $rol->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">{{ $rol->nombre }}</li>
@endsection

@section('content')
@php $puedeModificar = $nivelActor === 0 || $rol->nivel > $nivelActor; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-shield-check me-1"></i> {{ $rol->nombre }}
        <span class="badge bg-{{ $rol->activo ? 'success' : 'secondary' }} ms-2">
            {{ $rol->activo ? 'Activo' : 'Inactivo' }}
        </span>
        <span class="badge bg-secondary ms-1" title="Nivel jerárquico">Nivel {{ $rol->nivel }}</span>
    </h5>
    <div>
        @if($puedeModificar)
            <a href="{{ route('roles.edit', $rol) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
        @else
            <span class="btn btn-sm btn-outline-secondary disabled me-1">
                <i class="bi bi-lock-fill me-1"></i> Solo lectura
            </span>
        @endif
        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

@if($rol->descripcion)
    <p class="text-muted small mb-3">{{ $rol->descripcion }}</p>
@endif

{{-- Matriz de permisos (sólo lectura) --}}
<div class="card mb-3">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-key me-1"></i> Permisos por módulo
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th class="text-start ps-3" style="width:30%">Módulo</th>
                        <th style="width:17%"><i class="bi bi-eye me-1"></i>Ver</th>
                        <th style="width:17%"><i class="bi bi-plus-circle me-1"></i>Crear</th>
                        <th style="width:18%"><i class="bi bi-pencil me-1"></i>Editar</th>
                        <th style="width:18%"><i class="bi bi-trash me-1"></i>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulos as $clave => $etiqueta)
                        @php $p = $permisosIndexados[$clave] ?? null; @endphp
                        <tr>
                            <td class="text-start ps-3 fw-semibold small">{{ $etiqueta }}</td>
                            @foreach(['ver','crear','editar','eliminar'] as $accion)
                                <td>
                                    @if($p && $p->{"puede_{$accion}"})
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle text-secondary opacity-25"></i>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Usuarios asignados --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-people me-2"></i> Usuarios con este rol
        <span class="badge bg-light text-dark ms-auto">{{ $rol->asignaciones->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Institución</th>
                        <th class="text-center">Desde</th>
                        <th class="text-center">Hasta</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rol->asignaciones as $asig)
                        <tr>
                            <td>
                                @if($asig->usuario)
                                    <a href="{{ route('usuarios.show', $asig->usuario) }}" class="text-decoration-none">
                                        {{ $asig->usuario->apellidos }}, {{ $asig->usuario->nombres }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $asig->institucion?->nombre ?? '—' }}</td>
                            <td class="text-center small">{{ $asig->fecha_desde?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-center small">{{ $asig->fecha_hasta?->format('d/m/Y') ?? '∞' }}</td>
                            <td class="text-center">
                                @if($asig->estaVigente())
                                    <span class="badge bg-success">Vigente</span>
                                @else
                                    <span class="badge bg-secondary">Inactiva</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3 small">
                                Ningún usuario tiene este rol asignado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
