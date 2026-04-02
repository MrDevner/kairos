@extends('layouts.app')

@section('title', 'Designaciones')

@section('breadcrumb')
    <li class="breadcrumb-item active">Designaciones</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-briefcase-fill me-1"></i> Designaciones
    </h5>
    <a href="{{ route('designaciones.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nueva designación
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('designaciones.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por usuario o cargo…">
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
                <a href="{{ route('designaciones.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $designaciones->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Cargo</th>
                        <th>Institución</th>
                        <th>Dependencia</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Hs/sem</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designaciones as $des)
                        <tr>
                            <td>
                                <a href="{{ route('usuarios.show', $des->usuario) }}" class="text-decoration-none">
                                    {{ $des->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $des->cargo->nombre ?? '—' }}</td>
                            <td class="small">{{ $des->institucion->nombre ?? '—' }}</td>
                            <td class="small">{{ $des->dependencia->nombre ?? '—' }}</td>
                            <td class="small">{{ $des->fecha_inicio ? \Carbon\Carbon::parse($des->fecha_inicio)->format('d/m/Y') : '—' }}</td>
                            <td class="small">{{ $des->fecha_fin ? \Carbon\Carbon::parse($des->fecha_fin)->format('d/m/Y') : '—' }}</td>
                            <td class="text-center">
                                @if($des->activa && (!$des->fecha_fin || $des->fecha_fin >= now()->toDateString()))
                                    <span class="badge bg-success">Vigente</span>
                                @else
                                    <span class="badge bg-secondary">Finalizada</span>
                                @endif
                            </td>
                            <td class="text-center small">{{ $des->horas_semanales_efectivas ?? ($des->cargo->horas_semanales ?? '—') }}</td>
                            <td class="text-center">
                                <a href="{{ route('designaciones.show', $des) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('designaciones.edit', $des) }}" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('designaciones.destroy', $des) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta designación?')">
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
                                No se encontraron designaciones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($designaciones->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $designaciones->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
