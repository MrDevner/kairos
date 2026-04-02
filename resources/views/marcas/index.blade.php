@extends('layouts.app')

@section('title', 'Marcas')

@section('breadcrumb')
    <li class="breadcrumb-item active">Marcas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-clock-history me-1"></i> Marcas de asistencia
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('marcas.importar') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-upload me-1"></i> Importar
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('marcas.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Fecha</label>
                <input type="date" name="fecha" value="{{ request('fecha', today()->toDateString()) }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    <option value="entrada" @selected(request('tipo') === 'entrada')>Entrada</option>
                    <option value="salida"  @selected(request('tipo') === 'salida')>Salida</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Buscar usuario</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Nombre o documento…">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Proceso --}}
<div class="d-flex justify-content-end mb-2">
    <form method="POST" action="{{ route('marcas.procesar') }}"
          onsubmit="return confirm('¿Procesar marcas para la fecha seleccionada?')">
        @csrf
        <input type="hidden" name="fecha" value="{{ request('fecha', today()->toDateString()) }}">
        <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-gear me-1"></i> Procesar marcas
        </button>
    </form>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Resultados
        <span class="badge bg-light text-dark ms-auto">{{ $marcas->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Cargo</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th class="text-center">Min. Trabajados</th>
                        <th class="text-center">Min. Obligatorios</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center" style="width:80px">Errores</th>
                        <th class="text-center" style="width:80px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marcas as $marca)
                        <tr class="{{ $marca->tiene_error ? 'table-danger' : '' }}">
                            <td>
                                <a href="{{ route('usuarios.show', $marca->usuario) }}" class="text-decoration-none">
                                    {{ $marca->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $marca->designacion->cargo->nombre ?? '—' }}</td>
                            <td class="small">
                                {{ $marca->hora_entrada ? \Carbon\Carbon::parse($marca->hora_entrada)->format('H:i') : '—' }}
                            </td>
                            <td class="small">
                                {{ $marca->hora_salida ? \Carbon\Carbon::parse($marca->hora_salida)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center small">{{ $marca->minutos_trabajados ?? '—' }}</td>
                            <td class="text-center small">{{ $marca->minutos_obligatorios ?? '—' }}</td>
                            <td class="text-center">
                                @if($marca->tipo)
                                    <span class="badge bg-secondary small">{{ ucfirst($marca->tipo) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">
                                @if($marca->tiene_error)
                                    <i class="bi bi-exclamation-circle-fill text-danger" title="{{ $marca->error_descripcion ?? 'Error en marca' }}"></i>
                                @else
                                    <i class="bi bi-check-circle text-success"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('marcas.show', $marca) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron marcas para los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($marcas->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $marcas->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
