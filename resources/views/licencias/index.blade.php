@extends('layouts.app')

@section('title', 'Licencias')

@section('breadcrumb')
    <li class="breadcrumb-item active">Licencias</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-calendar-check-fill me-1"></i> Licencias
    </h5>
    <a href="{{ route('licencias.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nueva licencia
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
        <form method="GET" action="{{ route('licencias.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por apellido o nombre…">
            </div>
            <div class="col-sm-2">
                <select name="estado" class="form-select form-select-sm">
                    <option value="">— Todos los estados —</option>
                    <option value="pendiente"  @selected(request('estado') === 'pendiente')>Pendiente</option>
                    <option value="aprobada"   @selected(request('estado') === 'aprobada')>Aprobada</option>
                    <option value="rechazada"  @selected(request('estado') === 'rechazada')>Rechazada</option>
                </select>
            </div>
            <div class="col-sm-3">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">— Todos los tipos —</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}" @selected(request('tipo') == $tipo->id)>{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="date" name="desde" value="{{ request('desde') }}"
                       class="form-control form-control-sm" placeholder="Desde">
            </div>
            <div class="col-sm-2">
                <input type="date" name="hasta" value="{{ request('hasta') }}"
                       class="form-control form-control-sm" placeholder="Hasta">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('licencias.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $licencias->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Período</th>
                        <th class="text-center">Días</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($licencias as $lic)
                        @php
                            $badgeColor = ['pendiente'=>'warning','aprobada'=>'success','rechazada'=>'danger'][$lic->estado] ?? 'secondary';
                            $textColor  = $lic->estado === 'pendiente' ? 'dark' : 'white';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('usuarios.show', $lic->usuario) }}" class="text-decoration-none">
                                    {{ $lic->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $lic->tipoLicencia->nombre ?? '—' }}</td>
                            <td class="small font-monospace">
                                {{ $lic->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                @if($lic->fecha_fin)
                                    → {{ $lic->fecha_fin->format('d/m/Y') }}
                                @else
                                    → ∞
                                @endif
                            </td>
                            <td class="text-center small">
                                {{ $lic->dias_computados ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $badgeColor }} text-{{ $textColor }}">
                                    {{ ucfirst($lic->estado) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('licencias.show', $lic) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($lic->estaPendiente())
                                    <a href="{{ route('licencias.edit', $lic) }}"
                                       class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('licencias.destroy', $lic) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta licencia pendiente?')">
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
                                No se encontraron licencias.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($licencias->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $licencias->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
