@extends('layouts.app')

@section('title', 'Avisos del personal')

@section('breadcrumb')
    <li class="breadcrumb-item active">Avisos del personal</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-megaphone-fill me-1"></i> Avisos del personal
    </h5>
    @if($puedeCrear)
        <a href="{{ route('avisos.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-plus-lg me-1"></i> Registrar aviso
        </a>
    @endif
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
        <form method="GET" action="{{ route('avisos.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0">Evento desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0">Evento hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    <option value="ausencia"  @selected(request('tipo') === 'ausencia')>Ausencia</option>
                    <option value="tardanza"  @selected(request('tipo') === 'tardanza')>Tardanza</option>
                </select>
            </div>
            @if($dependencias->isNotEmpty())
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0">Dependencia</label>
                <select name="dependencia" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    @foreach($dependencias as $dep)
                        <option value="{{ $dep->id }}" @selected(request('dependencia') == $dep->id)>{{ $dep->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('avisos.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $avisos->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Fecha evento</th>
                        <th>Empleado</th>
                        <th>Dependencia</th>
                        <th class="text-center">Tipo</th>
                        <th>Detalle</th>
                        <th class="small text-muted">Registrado por</th>
                        <th class="text-center" style="width:90px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avisos as $aviso)
                        <tr>
                            <td class="text-center small">
                                <span class="font-monospace">{{ $aviso->fecha_evento?->format('d/m/Y') }}</span>
                                @if($aviso->fecha_aviso && $aviso->fecha_evento && $aviso->fecha_aviso->lt($aviso->fecha_evento))
                                    <br><span class="text-muted" style="font-size:.7rem" title="Aviso dado el {{ $aviso->fecha_aviso->format('d/m/Y') }}">
                                        <i class="bi bi-bell-fill"></i> {{ $aviso->fecha_aviso->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('usuarios.show', $aviso->usuario) }}" class="text-decoration-none">
                                    {{ $aviso->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $aviso->designacion?->dependencia?->nombre ?? '—' }}</td>
                            <td class="text-center">
                                @if($aviso->tipo === 'ausencia')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-calendar-x me-1"></i>Ausencia
                                    </span>
                                @else
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-clock me-1"></i>Tardanza
                                    </span>
                                @endif
                            </td>
                            <td class="small">
                                @if($aviso->tipo === 'ausencia')
                                    {{ $aviso->tipoLicencia?->nombre ?? '—' }}
                                @else
                                    {{ $aviso->hora_estimada_llegada ? 'Llega aprox. ' . \Carbon\Carbon::parse($aviso->hora_estimada_llegada)->format('H:i') : '—' }}
                                @endif
                                @if($aviso->motivo)
                                    <span class="text-muted ms-1" title="{{ $aviso->motivo }}">
                                        <i class="bi bi-chat-text"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $aviso->registradoPor?->nombre_completo ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('avisos.show', $aviso) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('editar', $aviso)
                                    <a href="{{ route('avisos.edit', $aviso) }}"
                                       class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('avisos.destroy', $aviso) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este aviso?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron avisos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($avisos->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $avisos->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
