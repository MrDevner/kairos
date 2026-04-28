@extends('layouts.app')

@section('title', 'Resumen por dependencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('informes.index') }}">Informes</a></li>
    <li class="breadcrumb-item active">Resumen por dependencia</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-diagram-3-fill me-1"></i> Resumen por dependencia
    </h5>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('informes.resumen-dependencia') }}" class="row g-2 align-items-end">

            <div class="col-sm-4">
                <label class="form-label form-label-sm small mb-0">Institución</label>
                <select name="institucion" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected($instId === $inst->id)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-2">
                <label class="form-label form-label-sm small mb-0">Mes</label>
                <select name="mes" class="form-select form-select-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" @selected($mes === $m)>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-2">
                <label class="form-label form-label-sm small mb-0">Año</label>
                <select name="anio" class="form-select form-select-sm">
                    @foreach(range(now()->year, now()->year - 4) as $y)
                        <option value="{{ $y }}" @selected($anio === $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Consultar
                </button>
                <a href="{{ route('informes.resumen-dependencia') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

@if($instId && $filas->isEmpty())
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        La institución seleccionada no tiene dependencias activas.
    </div>
@elseif(!$instId)
    <div class="alert alert-secondary">
        <i class="bi bi-arrow-up-circle me-1"></i>
        Seleccioná una institución para ver el resumen.
    </div>
@else
    {{-- Período --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="badge bg-secondary">
            <i class="bi bi-calendar3 me-1"></i>
            {{ $fechaDesde->translatedFormat('F Y') }}
        </span>
        <span class="small text-muted">
            {{ $fechaDesde->format('d/m/Y') }} — {{ $fechaHasta->format('d/m/Y') }}
        </span>
    </div>

    {{-- Totales --}}
    @php
        $totEmp  = $filas->sum('empleados');
        $totAus  = $filas->sum('ausencias');
        $totTar  = $filas->sum('tardanzas');
        $totLic  = $filas->sum('licencias');
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-6 col-sm-3">
            <div class="card text-center py-2">
                <div class="fs-4 fw-bold" style="color:var(--azul)">{{ $totEmp }}</div>
                <div class="small text-muted">Empleados activos</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card text-center py-2">
                <div class="fs-4 fw-bold text-danger">{{ $totAus }}</div>
                <div class="small text-muted">Avisos de ausencia</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card text-center py-2">
                <div class="fs-4 fw-bold text-warning">{{ $totTar }}</div>
                <div class="small text-muted">Avisos de tardanza</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card text-center py-2">
                <div class="fs-4 fw-bold text-success">{{ $totLic }}</div>
                <div class="small text-muted">Licencias aprobadas</div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
            <i class="bi bi-list-ul me-2"></i> Detalle por dependencia
            <span class="badge bg-light text-dark ms-auto">{{ $filas->count() }} dependencias</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Dependencia</th>
                            <th class="text-center">Empleados<br><small class="text-muted fw-normal">activos</small></th>
                            <th class="text-center">Ausencias</th>
                            <th class="text-center">Tardanzas</th>
                            <th class="text-center">Licencias<br><small class="text-muted fw-normal">aprobadas</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filas as $fila)
                            <tr>
                                <td>
                                    <a href="{{ route('dependencias.show', $fila['dep']) }}" class="text-decoration-none fw-semibold">
                                        {{ $fila['dep']->nombre }}
                                    </a>
                                    @if($fila['dep']->sigla)
                                        <span class="text-muted small">({{ $fila['dep']->sigla }})</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold" style="color:var(--azul)">
                                    {{ $fila['empleados'] }}
                                </td>
                                <td class="text-center">
                                    @if($fila['ausencias'] > 0)
                                        <span class="badge bg-danger">{{ $fila['ausencias'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($fila['tardanzas'] > 0)
                                        <span class="badge bg-warning text-dark">{{ $fila['tardanzas'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($fila['licencias'] > 0)
                                        <span class="badge bg-success">{{ $fila['licencias'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td>Total</td>
                            <td class="text-center">{{ $totEmp }}</td>
                            <td class="text-center">{{ $totAus ?: '—' }}</td>
                            <td class="text-center">{{ $totTar ?: '—' }}</td>
                            <td class="text-center">{{ $totLic ?: '—' }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
