@extends('layouts.app')
@section('title', 'Instituciones')
@section('breadcrumb')
    <li class="breadcrumb-item active">Instituciones</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-building me-2" style="color:var(--azul)"></i>Instituciones</h5>
    @if(auth()->user()->hasRole('Administrador General'))
        <a href="{{ route('instituciones.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-plus-lg"></i> Nueva institución
        </a>
    @endif
</div>

{{-- Búsqueda --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('instituciones.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-6">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por nombre o sigla…">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('instituciones.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

@if(isset($instituciones))
    {{-- Modo búsqueda: lista plana con paginación --}}
    <div class="card">
        <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
            <i class="bi bi-search me-2"></i> Resultados para «{{ request('buscar') }}»
            <span class="badge bg-light text-dark ms-auto">{{ $instituciones->total() }} registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40%">Nombre</th>
                            <th>Sigla</th>
                            <th>Institución padre</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instituciones as $inst)
                            <tr>
                                <td>
                                    <a href="{{ route('instituciones.show', $inst) }}" class="text-decoration-none fw-semibold">
                                        {{ $inst->nombre }}
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary">{{ $inst->sigla ?? '—' }}</span></td>
                                <td class="small">{{ $inst->padre?->nombre ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $inst->activa ? 'success' : 'secondary' }}">
                                        {{ $inst->activa ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('instituciones.show', $inst) }}"
                                       class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->hasRole('Administrador General'))
                                        <a href="{{ route('instituciones.edit', $inst) }}"
                                           class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('instituciones.destroy', $inst) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar «{{ addslashes($inst->nombre) }}»?')">
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                    No se encontraron instituciones.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($instituciones->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $instituciones->withQueryString()->links() }}
            </div>
        @endif
    </div>
@else
    {{-- Modo árbol --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40%">Nombre</th>
                            <th>Sigla</th>
                            <th>Institución padre</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arbol as $inst)
                            @include('instituciones._fila', ['inst' => $inst, 'nivel' => 0])
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Sin instituciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
