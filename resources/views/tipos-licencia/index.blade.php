@extends('layouts.app')

@section('title', 'Tipos de licencia')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tipos de licencia</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-card-list me-1"></i> Tipos de licencia
    </h5>
    <a href="{{ route('tipos-licencia.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo tipo
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('tipos-licencia.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre o descripción…">
            </div>
            <div class="col-sm-2">
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">— Categoría —</option>
                    <option value="todas" @selected(request('categoria') === 'todas')>Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(request('categoria') == $cat->id)>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="institucion" class="form-select form-select-sm">
                    <option value="">— Institución —</option>
                    <option value="global" @selected(request('institucion') === 'global')>Global</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(request('institucion') == $inst->id)>
                            {{ $inst->nombre }}
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
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('tipos-licencia.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $tipos->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Cómputo</th>
                        <th>Afecta</th>
                        <th class="text-center">Días máx.</th>
                        <th>Institución</th>
                        <th class="text-center">Doc.</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tipos as $t)
                        <tr>
                            <td class="fw-semibold">{{ $t->nombre }}</td>
                            <td class="small">
                                @if($t->categoriaCargo)
                                    <span class="badge bg-primary">{{ $t->categoriaCargo->nombre }}</span>
                                @else
                                    <span class="text-muted small">Todas</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $t->esDiasCorridos() ? 'Corridos' : 'Hábiles' }}
                            </td>
                            <td class="small">
                                {{ $t->afectaUsuario() ? 'Usuario' : 'Designación' }}
                            </td>
                            <td class="text-center small">{{ $t->dias_maximos ?? '—' }}</td>
                            <td class="small">
                                @if($t->institucion)
                                    <i class="bi bi-building text-secondary me-1"></i>{{ $t->institucion->nombre }}
                                @else
                                    <span class="badge bg-secondary">Global</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($t->requiere_documentacion)
                                    <i class="bi bi-paperclip text-warning" title="Requiere documentación"></i>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($t->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('tipos-licencia.show', $t) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('tipos-licencia.edit', $t) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('tipos-licencia.destroy', $t) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar «{{ addslashes($t->nombre) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron tipos de licencia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tipos->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $tipos->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
