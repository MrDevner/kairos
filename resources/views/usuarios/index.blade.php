@extends('layouts.app')

@section('title', 'Usuarios')

@section('breadcrumb')
    <li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-people-fill me-1"></i>
        @if($verTodos ?? false)
            Todos los usuarios del sistema
        @else
            Usuarios
        @endif
    </h5>
    <a href="{{ route('usuarios.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo usuario
    </a>
</div>

@if($verTodos ?? false)
    <div class="alert alert-info py-2 small mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Mostrando <strong>todos</strong> los usuarios del sistema, sin filtrar por institución.
        <a href="{{ route('usuarios.index') }}" class="ms-2">Ver sólo mi institución</a>
    </div>
@endif

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 align-items-end">
            @if($verTodos ?? false)
                <input type="hidden" name="todos" value="1">
            @endif
            <div class="col-sm-5">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre, apellido o documento…">
            </div>
            <div class="col-sm-3">
                <select name="activo" class="form-select form-select-sm">
                    <option value="">— Todos los estados —</option>
                    <option value="1" @selected(request('activo') === '1')>Activos</option>
                    <option value="0" @selected(request('activo') === '0')>Inactivos</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('usuarios.index', ($verTodos ?? false) ? ['todos' => 1] : []) }}"
                   class="btn btn-sm btn-outline-secondary ms-1">
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
                                    <div style="width:34px;height:34px;border-radius:50%;background:var(--azul);
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
