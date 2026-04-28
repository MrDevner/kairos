@extends('layouts.app')
@section('title', 'Detalle de marca')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas del personal</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
{{-- Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div class="d-flex align-items-center gap-3">
        <div style="width:50px;height:50px;border-radius:50%;background:var(--azul);color:#fff;
                    display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.15rem">
            {{ strtoupper(substr($marca->usuario->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($marca->usuario->apellidos ?? '', 0, 1)) }}
        </div>
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--azul)">{{ $marca->usuario->nombre_completo ?? '—' }}</h5>
            <div class="small text-muted">
                {{ $marca->fecha_hora->format('d/m/Y H:i:s') }}
                &mdash; {{ $marca->dispositivo->nombre ?? 'Dispositivo desconocido' }}
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('marcas.edit', $marca) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Datos de la marca original --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-fingerprint me-1"></i> Marca registrada
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Empleado</dt>
                    <dd class="col-7">{{ $marca->usuario->nombre_completo ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Documento</dt>
                    <dd class="col-7">{{ $marca->usuario->documento ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Fecha / Hora</dt>
                    <dd class="col-7 fw-semibold">{{ $marca->fecha_hora->format('d/m/Y H:i:s') }}</dd>

                    <dt class="col-5 text-muted">Dispositivo</dt>
                    <dd class="col-7">{{ $marca->dispositivo->nombre ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Tipo captura</dt>
                    <dd class="col-7">
                        @php
                            $colorTipo = match($marca->tipo_captura) {
                                'automatica' => 'bg-primary',
                                'web'        => 'bg-info text-dark',
                                default      => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $colorTipo }}">{{ ucfirst($marca->tipo_captura) }}</span>
                    </dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        @if($marca->procesada)
                            <span class="badge bg-success">Procesada</span>
                        @else
                            <span class="badge bg-warning text-dark">Sin procesar</span>
                        @endif
                    </dd>

                    @if($marca->datos_raw)
                        <dt class="col-5 text-muted">Datos raw</dt>
                        <dd class="col-7">
                            <pre class="mb-0 small bg-light rounded p-1" style="font-size:.73rem;max-height:80px;overflow:auto">{{ json_encode($marca->datos_raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 py-2">
                <form method="POST" action="{{ route('marcas.destroy', $marca) }}"
                      onsubmit="return confirm('¿Eliminar esta marca?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Marcas computadas del mismo día --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-calculator me-1"></i> Marcas computadas del mismo día
                <span class="badge bg-light text-dark ms-auto">{{ $computadas->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($computadas->isEmpty())
                    <p class="text-muted text-center small py-4 mb-0">
                        <i class="bi bi-hourglass-split fs-4 d-block mb-1"></i>
                        No hay marcas computadas para este día.<br>
                        <a href="#panelProcesar" data-bs-toggle="collapse" class="small">Procesar marcas</a>
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Cargo / Designación</th>
                                    <th class="text-center">Entrada</th>
                                    <th class="text-center">Salida</th>
                                    <th class="text-center">Min. trabajados</th>
                                    <th class="text-center">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($computadas as $comp)
                                    <tr class="{{ $comp->tiene_error ? 'table-danger' : '' }}">
                                        <td class="small">{{ $comp->designacion->cargo->nombre ?? '—' }}</td>
                                        <td class="text-center small">
                                            {{ $comp->hora_entrada ? \Carbon\Carbon::parse($comp->hora_entrada)->format('H:i') : '—' }}
                                        </td>
                                        <td class="text-center small">
                                            {{ $comp->hora_salida ? \Carbon\Carbon::parse($comp->hora_salida)->format('H:i') : '—' }}
                                        </td>
                                        <td class="text-center small">{{ $comp->minutos_trabajados ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary small">{{ ucfirst($comp->tipo ?? '—') }}</span>
                                        </td>
                                    </tr>
                                    @if($comp->tiene_error && !empty($comp->errores))
                                        <tr class="table-danger">
                                            <td colspan="5" class="small text-danger py-1">
                                                <i class="bi bi-exclamation-circle me-1"></i>
                                                {{ implode(' | ', $comp->errores) }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if($comp->tiene_observacion && !empty($comp->observaciones))
                                        <tr class="table-warning">
                                            <td colspan="5" class="small text-warning-emphasis py-1">
                                                <i class="bi bi-info-circle me-1"></i>
                                                {{ implode(' | ', $comp->observaciones) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
