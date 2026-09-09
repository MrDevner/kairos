@extends('layouts.app')

@section('title', 'Usuarios')

@section('breadcrumb')
    <li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
@php
    $filtrosActivos = collect([request('buscar'), request('activo')])
        ->filter(fn ($v) => $v !== null && $v !== '')
        ->count();
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="k2-page-title">
        <i class="bi bi-people-fill me-1"></i>
        @if($verTodos ?? false)
            Todos los usuarios del sistema
        @else
            Usuarios
        @endif
    </h5>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary position-relative"
                data-bs-toggle="modal" data-bs-target="#modalFiltros">
            <i class="bi bi-funnel me-1"></i> Filtrar
            @if($filtrosActivos > 0)
                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle"
                      style="font-size:.6rem">{{ $filtrosActivos }}</span>
            @endif
        </button>
        <a href="{{ route('usuarios.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo usuario
        </a>
    </div>
</div>

@if($verTodos ?? false)
    <div class="alert alert-info py-2 small mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Mostrando <strong>todos</strong> los usuarios del sistema, sin filtrar por institución.
        <a href="{{ route('usuarios.index') }}" class="ms-2">Ver sólo mi institución</a>
    </div>
@endif

{{-- Modal de filtros --}}
<div class="modal fade" id="modalFiltros" tabindex="-1" aria-labelledby="modalFiltrosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="{{ route('usuarios.index') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFiltrosLabel">
                        <i class="bi bi-funnel me-1"></i> Filtrar usuarios
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    @if($verTodos ?? false)
                        <input type="hidden" name="todos" value="1">
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Buscar</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               class="form-control form-control-sm" placeholder="Nombre, apellido o documento…">
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Estado</label>
                        <select name="activo" class="form-select form-select-sm">
                            <option value="">— Todos los estados —</option>
                            <option value="1" @selected(request('activo') === '1')>Activos</option>
                            <option value="0" @selected(request('activo') === '0')>Inactivos</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('usuarios.index', ($verTodos ?? false) ? ['todos' => 1] : []) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-list-ul me-2"></i> Listado
        <span class="badge bg-light text-dark ms-auto">{{ $usuarios->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">Foto</th>
                        <th>Apellidos y Nombres</th>
                        <th>Documento</th>
                        <th>Email</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        <tr>
                            <td class="text-center">
                                @if($u->foto)
                                    <img src="{{ asset('storage/' . $u->foto) }}" alt=""
                                         style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--celeste)">
                                @else
                                    <div style="width:34px;height:34px;border-radius:50%;background:var(--k2-primary);
                                                color:#fff;display:flex;align-items:center;justify-content:center;
                                                font-weight:700;font-size:.75rem;margin:0 auto">
                                        {{ strtoupper(substr($u->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($u->apellidos ?? '', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('usuarios.show', $u) }}" class="text-decoration-none fw-semibold">
                                    {{ $u->apellidos }}, {{ $u->nombres }}
                                </a>
                            </td>
                            <td class="small">{{ $u->documento ?? '—' }}</td>
                            <td class="small">{{ $u->email ?? '—' }}</td>
                            <td class="text-center">
                                @if($u->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('usuarios.show', $u) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('usuarios.destroy', $u) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar usuario «{{ addslashes($u->nombre_completo) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($usuarios->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $usuarios->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
