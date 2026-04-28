@extends('layouts.app')
@section('title', 'Mensual de marcas')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mensual de marcas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-clock-history me-1"></i> Mensual de marcas
    </h5>
    <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-fingerprint me-1"></i> Ver registros originales
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('informes.marcas') }}" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Fecha</label>
                <input type="date" name="fecha" value="{{ $fecha }}" class="form-control form-control-sm">
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
                <label class="form-label form-label-sm mb-0 small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    @foreach(['normal','tardanza','ausencia','licencia','feriado','suspension','sin_obligacion'] as $t)
                        <option value="{{ $t }}" @selected(request('tipo') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('informes.marcas') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Procesar --}}
<div class="d-flex justify-content-end mb-2">
    <form method="POST" action="{{ route('marcas.procesar') }}"
          onsubmit="return confirm('¿Procesar marcas para la fecha seleccionada?')">
        @csrf
        <input type="hidden" name="fecha" value="{{ $fecha }}">
        <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-gear me-1"></i> Procesar marcas de esta fecha
        </button>
    </form>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Resultados — {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
        <span class="badge bg-light text-dark ms-auto">{{ $marcas->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th class="text-center">Entrada</th>
                        <th class="text-center">Salida</th>
                        <th class="text-center">Min. Trab.</th>
                        <th class="text-center">Min. Oblig.</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center" style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marcas as $mc)
                        <tr class="{{ $mc->tiene_error ? 'table-danger' : '' }}">
                            <td>
                                <a href="{{ route('usuarios.show', $mc->usuario) }}" class="text-decoration-none fw-semibold">
                                    {{ $mc->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $mc->designacion->cargo->nombre ?? '—' }}</td>
                            <td class="text-center small">
                                {{ $mc->hora_entrada ? \Carbon\Carbon::parse($mc->hora_entrada)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center small">
                                {{ $mc->hora_salida ? \Carbon\Carbon::parse($mc->hora_salida)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center small">{{ $mc->minutos_trabajados ?? '—' }}</td>
                            <td class="text-center small">{{ $mc->minutos_obligatorios ?? '—' }}</td>
                            <td class="text-center">
                                @php
                                    $badgeColor = match($mc->tipo ?? '') {
                                        'normal'         => 'bg-success',
                                        'tardanza'       => 'bg-warning text-dark',
                                        'ausencia'       => 'bg-danger',
                                        'licencia'       => 'bg-info text-dark',
                                        'feriado'        => 'bg-primary',
                                        'suspension'     => 'bg-dark',
                                        'sin_obligacion' => 'bg-light text-dark border',
                                        default          => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }} small">{{ ucfirst($mc->tipo ?? '—') }}</span>
                            </td>
                            <td class="text-center">
                                @if($mc->tiene_error)
                                    <i class="bi bi-exclamation-circle-fill text-danger"
                                       title="{{ implode(' | ', $mc->errores ?? []) }}"></i>
                                @elseif($mc->tiene_observacion)
                                    <i class="bi bi-info-circle-fill text-warning"
                                       title="{{ implode(' | ', $mc->observaciones ?? []) }}"></i>
                                @else
                                    <i class="bi bi-check-circle text-success"></i>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron marcas computadas para los filtros aplicados.
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
