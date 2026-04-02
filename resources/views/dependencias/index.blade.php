@extends('layouts.app')

@section('title', 'Dependencias')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dependencias</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-diagram-3-fill me-1"></i> Dependencias
    </h5>
    <a href="{{ route('dependencias.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nueva dependencia
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('dependencias.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-5">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre o sigla…">
            </div>
            <div class="col-sm-4">
                <select name="institucion" class="form-select form-select-sm">
                    <option value="">— Todas las instituciones —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(request('institucion') == $inst->id)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('dependencias.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $dependencias->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Sigla</th>
                        <th>Institución</th>
                        <th>Padre</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dependencias as $dep)
                        <tr>
                            <td>{{ $dep->nombre }}</td>
                            <td><span class="badge bg-secondary">{{ $dep->sigla ?? '—' }}</span></td>
                            <td>{{ $dep->institucion->nombre ?? '—' }}</td>
                            <td>
                                @if($dep->padre)
                                    <a href="{{ route('dependencias.show', $dep->padre) }}" class="text-decoration-none">
                                        {{ $dep->padre->nombre }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($dep->activa)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-secondary">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('dependencias.show', $dep) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('dependencias.edit', $dep) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('dependencias.destroy', $dep) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar dependencia «{{ addslashes($dep->nombre) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron dependencias.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($dependencias->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $dependencias->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
