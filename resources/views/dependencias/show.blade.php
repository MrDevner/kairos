@extends('layouts.app')

@section('title', $dependencia->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dependencias.index') }}">Dependencias</a></li>
    <li class="breadcrumb-item active">{{ $dependencia->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-diagram-3-fill me-1"></i> {{ $dependencia->nombre }}
        @if($dependencia->sigla)
            <span class="badge bg-secondary ms-1">{{ $dependencia->sigla }}</span>
        @endif
    </h5>
    <div>
        <a href="{{ route('dependencias.edit', $dependencia) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('dependencias.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Columna izquierda: datos --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    @if($dependencia->descripcion)
                        <dt class="col-5 text-muted">Descripción</dt>
                        <dd class="col-7">{{ $dependencia->descripcion }}</dd>
                    @endif

                    <dt class="col-5 text-muted">Institución</dt>
                    <dd class="col-7">
                        @if($dependencia->institucion)
                            <a href="{{ route('instituciones.show', $dependencia->institucion) }}" class="text-decoration-none">
                                {{ $dependencia->institucion->nombre }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Dependencia padre</dt>
                    <dd class="col-7">
                        @if($dependencia->padre)
                            <a href="{{ route('dependencias.show', $dependencia->padre) }}" class="text-decoration-none">
                                {{ $dependencia->padre->nombre }}
                            </a>
                        @else
                            <span class="text-muted">— (raíz)</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $dependencia->activa ? 'success' : 'secondary' }}">
                            {{ $dependencia->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Sub-dependencias --}}
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-diagram-2 me-1"></i> Sub-dependencias
                <span class="badge bg-light text-dark ms-auto">{{ $dependencia->hijos->count() }}</span>
            </div>
            @if($dependencia->hijos->isEmpty())
                <div class="card-body text-center text-muted small py-3">
                    <i class="bi bi-inbox d-block mb-1 fs-5"></i>
                    Sin sub-dependencias.
                </div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($dependencia->hijos as $hijo)
                        <li class="list-group-item d-flex align-items-center justify-content-between py-2 small">
                            <a href="{{ route('dependencias.show', $hijo) }}" class="text-decoration-none fw-semibold">
                                {{ $hijo->nombre }}
                                @if($hijo->sigla)
                                    <span class="text-muted">({{ $hijo->sigla }})</span>
                                @endif
                            </a>
                            <span class="badge bg-{{ $hijo->activa ? 'success' : 'secondary' }}">
                                {{ $hijo->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Columna derecha --}}
    <div class="col-md-8">
        {{-- Jefatura actual --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-person-badge me-1"></i> Jefatura actual
                <button type="button" class="btn btn-sm btn-light ms-auto"
                        data-bs-toggle="modal" data-bs-target="#modalAsignarJefe">
                    <i class="bi bi-person-plus me-1"></i> Asignar jefe
                </button>
            </div>
            <div class="card-body">
                @if($jefeActual)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:52px;height:52px;border-radius:50%;background:var(--azul);
                                    color:#fff;display:flex;align-items:center;justify-content:center;
                                    font-weight:700;font-size:1.1rem;flex-shrink:0">
                            {{ strtoupper(substr($jefeActual->usuario->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($jefeActual->usuario->apellidos ?? '', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">
                                <a href="{{ route('usuarios.show', $jefeActual->usuario) }}" class="text-decoration-none">
                                    {{ $jefeActual->usuario->nombre_completo }}
                                </a>
                            </div>
                            @if($jefeActual->cargo)
                                <div class="small text-muted">{{ $jefeActual->cargo }}</div>
                            @endif
                            <div class="small text-muted">
                                Desde {{ $jefeActual->fecha_desde->format('d/m/Y') }}
                                @if($jefeActual->fecha_hasta)
                                    — Hasta {{ $jefeActual->fecha_hasta->format('d/m/Y') }}
                                @endif
                            </div>
                        </div>
                        <form method="POST"
                              action="{{ route('dependencias.jefe.baja', $dependencia) }}"
                              onsubmit="return confirm('¿Dar de baja a {{ addslashes($jefeActual->usuario->nombre_completo) }} como jefe de esta dependencia?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Dar de baja">
                                <i class="bi bi-person-dash me-1"></i> Dar de baja
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-muted mb-0 text-center py-2">
                        <i class="bi bi-person-dash fs-4 d-block mb-1"></i>
                        Sin jefe asignado actualmente.
                    </p>
                @endif
            </div>
        </div>

        {{-- Historial de jefaturas --}}
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-clock-history me-1"></i> Historial de jefaturas
            </div>
            <div class="card-body p-0">
                @if($dependencia->jefaturas->isEmpty())
                    <p class="text-muted text-center py-3 mb-0 small">
                        <i class="bi bi-inbox d-block mb-1 fs-5"></i>
                        Sin historial de jefaturas.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Jefe</th>
                                    <th>Cargo/Función</th>
                                    <th>Desde</th>
                                    <th>Hasta</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dependencia->jefaturas as $jef)
                                    <tr>
                                        <td class="small">
                                            <a href="{{ route('usuarios.show', $jef->usuario) }}" class="text-decoration-none">
                                                {{ $jef->usuario->nombre_completo }}
                                            </a>
                                        </td>
                                        <td class="small">{{ $jef->cargo ?? '—' }}</td>
                                        <td class="small">{{ $jef->fecha_desde->format('d/m/Y') }}</td>
                                        <td class="small">{{ $jef->fecha_hasta?->format('d/m/Y') ?? 'Vigente' }}</td>
                                        <td class="text-center">
                                            @if($jef->estaVigente())
                                                <span class="badge bg-success">Vigente</span>
                                            @else
                                                <span class="badge bg-secondary">Finalizada</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal: Asignar nuevo jefe --}}
<div class="modal fade" id="modalAsignarJefe" tabindex="-1" aria-labelledby="modalAsignarJefeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dependencias.jefe', $dependencia) }}">
                @csrf
                <div class="modal-header" style="background:var(--azul);color:#fff">
                    <h6 class="modal-title" id="modalAsignarJefeLabel">
                        <i class="bi bi-person-badge me-1"></i> Asignar jefe — {{ $dependencia->nombre }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Usuario <span class="text-danger">*</span></label>
                        @php
                            $oldUidJefe  = old('id_usuario');
                            $oldUserJefe = $oldUidJefe ? \App\Models\Usuario::find($oldUidJefe) : null;
                        @endphp
                        <select id="sel-usuario-jefe" name="id_usuario" required>
                            @if($oldUserJefe)
                                <option value="{{ $oldUserJefe->id }}" selected>
                                    {{ $oldUserJefe->apellidos }}, {{ $oldUserJefe->nombres }}{{ $oldUserJefe->documento ? ' ('.$oldUserJefe->documento.')' : '' }}
                                </option>
                            @endif
                        </select>
                        @error('id_usuario')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Cargo / Función</label>
                        <input type="text" name="cargo" class="form-control form-control-sm"
                               value="{{ old('cargo') }}" placeholder="Ej: Director, Secretario…" maxlength="100">
                        @error('cargo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Fecha desde <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_desde" class="form-control form-control-sm"
                                   value="{{ old('fecha_desde', now()->toDateString()) }}" required>
                            @error('fecha_desde')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Fecha hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                                   value="{{ old('fecha_hasta') }}">
                            @error('fecha_hasta')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <p class="form-text mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        La jefatura vigente anterior será cerrada automáticamente.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                        <i class="bi bi-check-lg me-1"></i> Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select2 dentro del modal: inicializar cuando el modal esté visible
// para que dropdownParent resuelva correctamente el z-index
document.getElementById('modalAsignarJefe').addEventListener('shown.bs.modal', function () {
    initSelect2Usuario('#sel-usuario-jefe', {
        dropdownParent: $('#modalAsignarJefe'),
    });
});
</script>
@endpush
