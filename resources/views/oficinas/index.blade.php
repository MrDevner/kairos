@extends('layouts.app')

@section('title', 'Oficinas / Aulas')

@section('breadcrumb')
    <li class="breadcrumb-item active">Oficinas / Aulas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-door-open-fill me-1"></i> Oficinas / Aulas
    </h5>
    <a href="{{ route('oficinas.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nueva oficina
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('oficinas.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-5">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre…">
            </div>
            <div class="col-sm-4">
                <select name="edificio" class="form-select form-select-sm">
                    <option value="">— Todos los edificios —</option>
                    @foreach($edificios as $edif)
                        <option value="{{ $edif->id }}" @selected(request('edificio') == $edif->id)>
                            {{ $edif->nombre }} @if($edif->institucion) ({{ $edif->institucion->nombre }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('oficinas.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $oficinas->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Edificio</th>
                        <th>Institución</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oficinas as $of)
                        <tr>
                            <td>{{ $of->nombre }}</td>
                            <td>
                                <a href="{{ route('edificios.show', $of->edificio) }}" class="text-decoration-none">
                                    {{ $of->edificio->nombre ?? '—' }}
                                </a>
                            </td>
                            <td>{{ $of->edificio->institucion->nombre ?? '—' }}</td>
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
                                <form method="POST" action="{{ route('oficinas.destroy', $of) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar oficina «{{ addslashes($of->nombre) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron oficinas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($oficinas->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $oficinas->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
