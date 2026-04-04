@extends('layouts.app')

@section('title', 'Roles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-shield-check me-1"></i> Roles
    </h5>
    <a href="{{ route('roles.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo rol
    </a>
</div>

{{-- Búsqueda --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('roles.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-6">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre o descripción…">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Listado
        <span class="badge bg-light text-dark ms-auto">{{ $roles->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px" class="text-center">Nivel</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-center">Asignados</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                        @php $puedeModificar = $nivelActor === 0 || $rol->nivel > $nivelActor; @endphp
                        <tr class="{{ !$puedeModificar ? 'table-light' : '' }}">
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $rol->nivel }}</span>
                            </td>
                            <td class="fw-semibold">
                                <a href="{{ route('roles.show', $rol) }}" class="text-decoration-none">
                                    {{ $rol->nombre }}
                                </a>
                                @if(!$puedeModificar)
                                    <i class="bi bi-lock-fill text-muted ms-1" title="Jerarquía superior — solo lectura"></i>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $rol->descripcion ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $rol->asignaciones_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $rol->activo ? 'success' : 'secondary' }}">
                                    {{ $rol->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('roles.show', $rol) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($puedeModificar)
                                    <a href="{{ route('roles.edit', $rol) }}"
                                       class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('roles.destroy', $rol) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar rol «{{ addslashes($rol->nombre) }}»?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron roles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($roles->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $roles->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
