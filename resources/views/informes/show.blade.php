@extends('layouts.app')

@section('title', 'Informe — ' . ($informe->fecha ? \Carbon\Carbon::parse($informe->fecha)->format('d/m/Y') : ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('informes.index') }}">Informes</a></li>
    <li class="breadcrumb-item active">{{ $informe->fecha ? \Carbon\Carbon::parse($informe->fecha)->format('d/m/Y') : 'Informe' }}</li>
@endsection

@section('content')

@php
    $estadoBadge = ['pendiente'=>'warning','generado'=>'success','error'=>'danger'][$informe->estado] ?? 'secondary';
@endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--azul)">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>
            Informe de asistencia
            <span class="badge bg-{{ $estadoBadge }} ms-1">{{ ucfirst($informe->estado) }}</span>
        </h5>
        <div class="small text-muted">
            {{ $informe->institucion->nombre ?? '—' }} —
            {{ $informe->fecha ? \Carbon\Carbon::parse($informe->fecha)->format('d \d\e F \d\e Y') : '—' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('informes.excel', $informe) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel
        </a>
        <a href="{{ route('informes.pdf', $informe) }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
        </a>
        <a href="{{ route('informes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

{{-- Tarjetas de resumen --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md">
        <div class="card text-center border-success h-100">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold text-success">{{ $resumen['presentes'] ?? 0 }}</div>
                <div class="small text-muted"><i class="bi bi-person-check me-1"></i> Presentes</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="card text-center border-warning h-100">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold text-warning">{{ $resumen['tardanzas'] ?? 0 }}</div>
                <div class="small text-muted"><i class="bi bi-clock-history me-1"></i> Tardanzas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="card text-center border-danger h-100">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold text-danger">{{ $resumen['ausencias'] ?? 0 }}</div>
                <div class="small text-muted"><i class="bi bi-person-x me-1"></i> Ausencias</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="card text-center border-danger h-100">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold text-danger">{{ $resumen['urgentes'] ?? 0 }}</div>
                <div class="small text-muted"><i class="bi bi-exclamation-triangle me-1"></i> Urgentes</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="card text-center h-100" style="border-color:var(--celeste)">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold" style="color:var(--celeste)">{{ $resumen['licencias'] ?? 0 }}</div>
                <div class="small text-muted"><i class="bi bi-calendar-check me-1"></i> Licencias</div>
            </div>
        </div>
    </div>
</div>

{{-- Tabla de ítems --}}
<div class="card">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-table me-1"></i> Detalle por empleado
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Cargo</th>
                        <th class="text-center">Novedad</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th class="text-center">Min.</th>
                        <th class="text-center" title="Requiere atención">Atención</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            // Mapeo del colorClase() del modelo a clase Bootstrap
                            $claseMap = [
                                'verde'    => 'table-success',
                                'amarillo' => 'table-warning',
                                'rojo'     => 'table-danger',
                                'celeste'  => 'table-info',
                                'gris'     => '',
                            ];
                            $claseRow = $claseMap[$item->colorClase()] ?? '';

                            $novedadBadge = [
                                'presente'  => 'success',
                                'tardanza'  => 'warning',
                                'ausente'   => 'danger',
                                'licencia'  => 'info',
                                'urgente'   => 'danger',
                            ][$item->novedad ?? ''] ?? 'secondary';
                        @endphp
                        <tr class="{{ $claseRow }}">
                            <td>
                                <a href="{{ route('usuarios.show', $item->usuario) }}" class="text-decoration-none">
                                    {{ $item->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $item->cargo->nombre ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $novedadBadge }}">{{ ucfirst($item->novedad ?? '—') }}</span>
                            </td>
                            <td class="small">
                                {{ $item->hora_entrada ? \Carbon\Carbon::parse($item->hora_entrada)->format('H:i') : '—' }}
                            </td>
                            <td class="small">
                                {{ $item->hora_salida ? \Carbon\Carbon::parse($item->hora_salida)->format('H:i') : '—' }}
                            </td>
                            <td class="text-center small">{{ $item->minutos_trabajados ?? '—' }}</td>
                            <td class="text-center">
                                @if($item->requiere_atencion)
                                    <i class="bi bi-exclamation-circle-fill text-danger" title="Requiere atención"></i>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Sin datos para este informe.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
