@extends('layouts.app')

@section('title', 'Edificios / Complejos')

@section('breadcrumb')
    <li class="breadcrumb-item active">Edificios / Complejos</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-building me-1"></i> Edificios / Complejos
    </h5>
    <a href="{{ route('edificios.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo edificio
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('edificios.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-5">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre…">
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
                <a href="{{ route('edificios.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $edificios->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Institución</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($edificios as $edif)
                        <tr>
                            <td>{{ $edif->nombre }}</td>
                            <td>{{ $edif->institucion->nombre ?? '—' }}</td>
                            <td class="text-center">
                                @if($edif->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('edificios.show', $edif) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('edificios.edit', $edif) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('edificios.destroy', $edif) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar edificio «{{ addslashes($edif->nombre) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron edificios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($edificios->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $edificios->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
