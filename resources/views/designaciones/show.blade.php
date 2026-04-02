@extends('layouts.app')

@section('title', 'Designación — ' . ($designacion->usuario->nombre_completo ?? ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('designaciones.index') }}">Designaciones</a></li>
    <li class="breadcrumb-item active">{{ $designacion->usuario->nombre_completo ?? 'Designación' }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-briefcase me-1"></i> Detalle de Designación
    </h5>
    <div>
        <a href="{{ route('designaciones.edit', $designacion) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('designaciones.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos de la designación
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Usuario</dt>
                    <dd class="col-7">
                        <a href="{{ route('usuarios.show', $designacion->usuario) }}" class="text-decoration-none fw-semibold">
                            {{ $designacion->usuario->nombre_completo ?? '—' }}
                        </a>
                    </dd>

                    <dt class="col-5 text-muted">Cargo</dt>
                    <dd class="col-7">{{ $designacion->cargo->nombre ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Institución</dt>
                    <dd class="col-7">{{ $designacion->institucion->nombre ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Dependencia</dt>
                    <dd class="col-7">{{ $designacion->dependencia->nombre ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Fecha inicio</dt>
                    <dd class="col-7">{{ $designacion->fecha_inicio ? \Carbon\Carbon::parse($designacion->fecha_inicio)->format('d/m/Y') : '—' }}</dd>

                    <dt class="col-5 text-muted">Fecha fin</dt>
                    <dd class="col-7">{{ $designacion->fecha_fin ? \Carbon\Carbon::parse($designacion->fecha_fin)->format('d/m/Y') : 'Sin fecha de fin' }}</dd>

                    <dt class="col-5 text-muted">Resolución</dt>
                    <dd class="col-7">{{ $designacion->resolucion ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Horas/sem (cargo)</dt>
                    <dd class="col-7">{{ $designacion->cargo->horas_semanales ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Horas/sem (efectivas)</dt>
                    <dd class="col-7 fw-semibold">
                        {{ $designacion->horasSemanalesObligatorias() }}
                        <span class="text-muted fw-normal">(calculadas)</span>
                    </dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        @if($designacion->activa && (!$designacion->fecha_fin || $designacion->fecha_fin >= now()->toDateString()))
                            <span class="badge bg-success">Vigente</span>
                        @else
                            <span class="badge bg-secondary">Finalizada</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-file-text me-1"></i> Últimas Declaraciones Juradas
            </div>
            <div class="card-body p-0">
                @php $ddjjs = $designacion->declaracionesJuradas->take(5); @endphp
                @if($ddjjs->isEmpty())
                    <p class="text-muted text-center py-3 mb-0 small">Sin declaraciones juradas registradas.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Período</th>
                                    <th class="text-center">Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ddjjs as $ddjj)
                                    <tr>
                                        <td class="small">
                                            {{ \Carbon\Carbon::parse($ddjj->fecha_inicio)->format('d/m/Y') }}
                                            — {{ \Carbon\Carbon::parse($ddjj->fecha_fin)->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $estadoBadge = [
                                                    'borrador'   => 'secondary',
                                                    'presentada' => 'warning',
                                                    'aprobada'   => 'success',
                                                    'rechazada'  => 'danger',
                                                ][$ddjj->estado] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $estadoBadge }}">{{ ucfirst($ddjj->estado) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('ddjj.show', $ddjj) }}" class="btn btn-sm btn-outline-primary py-0 px-1">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
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
