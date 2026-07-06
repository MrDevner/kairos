@extends('layouts.app')
@section('title', 'Cargos')

@section('breadcrumb')
    <li class="breadcrumb-item active">Cargos</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-briefcase me-1"></i> Cargos
    </h5>
    @if(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos())
    <div>
        <a href="{{ route('categorias-cargo.index') }}" class="btn btn-sm btn-outline-secondary me-1">
            <i class="bi bi-tags me-1"></i> Categorías
        </a>
        <a href="{{ route('cargos.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-plus-lg me-1"></i> Nuevo cargo
        </a>
    </div>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 small">
        {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('cargos.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre…">
            </div>
            <div class="col-sm-3">
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">— Todas las categorías —</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(request('categoria') == $cat->id)>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="activo" class="form-select form-select-sm">
                    <option value="">— Estado —</option>
                    <option value="1" @selected(request('activo') === '1')>Activo</option>
                    <option value="0" @selected(request('activo') === '0')>Inactivo</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('cargos.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Listado
        <span class="badge bg-light text-dark ms-auto">{{ $cargos->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th class="text-center">Hs/sem.</th>
                        <th class="text-center">Índice</th>
                        <th class="text-center">Estado</th>
                        @if(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos())
                        <th class="text-center" style="width:90px">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($cargos as $c)
                        <tr>
                            <td class="fw-semibold">{{ $c->nombre }}</td>
                            <td>
                                @if($c->categoria)
                                    <span class="badge" style="background:var(--celeste);color:var(--azul)">
                                        {{ $c->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace">{{ $c->horas_semanales }}</td>
                            <td class="text-center font-monospace">
                                {{ $c->indice ? number_format($c->indice, 4) : '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $c->activo ? 'success' : 'secondary' }}">
                                    {{ $c->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            @if(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos())
                            <td class="text-center">
                                <a href="{{ route('cargos.edit', $c) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('cargos.destroy', $c) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar «{{ addslashes($c->nombre) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron cargos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cargos->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $cargos->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
