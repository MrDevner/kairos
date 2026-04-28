@extends('layouts.app')
@section('title', 'Marcas del personal')

@section('breadcrumb')
    <li class="breadcrumb-item active">Marcas del personal</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-fingerprint me-1"></i> Marcas del personal
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('marcas.importar') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-upload me-1"></i> Importar
        </a>
        <a href="{{ route('marcas.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-plus-lg me-1"></i> Nueva marca
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('marcas.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Empleado</label>
                <select name="id_usuario" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected(request('id_usuario') == $u->id)>
                            {{ $u->apellidos }}, {{ $u->nombres }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Tipo captura</label>
                <select name="tipo_captura" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    <option value="automatica" @selected(request('tipo_captura') === 'automatica')>Automática</option>
                    <option value="importada"  @selected(request('tipo_captura') === 'importada')>Importada</option>
                    <option value="web"        @selected(request('tipo_captura') === 'web')>Web</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Estado</label>
                <select name="procesada" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    <option value="0" @selected(request('procesada') === '0')>Sin procesar</option>
                    <option value="1" @selected(request('procesada') === '1')>Procesada</option>
                </select>
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

{{-- Procesar marcas --}}
<div class="d-flex justify-content-end mb-2">
    <button class="btn btn-sm btn-outline-primary" type="button"
            data-bs-toggle="collapse" data-bs-target="#panelProcesar">
        <i class="bi bi-gear me-1"></i> Procesar marcas
    </button>
</div>
<div class="collapse mb-3" id="panelProcesar">
    <div class="card border-primary">
        <div class="card-body py-2">
            <form method="POST" action="{{ route('marcas.procesar') }}"
                  onsubmit="return confirm('¿Procesar marcas originales para la fecha indicada?')"
                  class="row g-2 align-items-end">
                @csrf
                <div class="col-sm-3">
                    <label class="form-label form-label-sm mb-0 small">Fecha a procesar</label>
                    <input type="date" name="fecha" value="{{ $desde }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-sm-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-play-fill me-1"></i> Procesar
                    </button>
                </div>
                <div class="col-12">
                    <p class="text-muted small mb-0">
                        Calcula las marcas computadas (entrada/salida, minutos, tipo) a partir de las marcas originales no procesadas de la fecha indicada.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Registros
        <span class="badge bg-light text-dark ms-auto">{{ $marcas->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Empleado</th>
                        <th>Fecha / Hora</th>
                        <th>Dispositivo</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marcas as $m)
                        <tr>
                            <td>
                                <a href="{{ route('marcas.show', $m) }}" class="text-decoration-none fw-semibold">
                                    {{ $m->usuario->nombre_completo ?? '—' }}
                                </a>
                                @if($m->usuario->documento)
                                    <div class="text-muted small">{{ $m->usuario->documento }}</div>
                                @endif
                            </td>
                            <td class="small">
                                <span class="fw-semibold">{{ $m->fecha_hora->format('d/m/Y') }}</span>
                                <span class="text-muted ms-1">{{ $m->fecha_hora->format('H:i:s') }}</span>
                            </td>
                            <td class="small">{{ $m->dispositivo->nombre ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $colorTipo = match($m->tipo_captura) {
                                        'automatica' => 'bg-primary',
                                        'web'        => 'bg-info text-dark',
                                        default      => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $colorTipo }} small">{{ ucfirst($m->tipo_captura) }}</span>
                            </td>
                            <td class="text-center">
                                @if($m->procesada)
                                    <span class="badge bg-success small">Procesada</span>
                                @else
                                    <span class="badge bg-warning text-dark small">Sin procesar</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('marcas.show', $m) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('marcas.edit', $m) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('marcas.destroy', $m) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta marca?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
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
