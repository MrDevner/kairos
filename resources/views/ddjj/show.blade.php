@extends('layouts.app')

@section('title', 'DDJJ — ' . \Carbon\Carbon::parse($ddjj->fecha_inicio)->format('d/m/Y'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ddjj.index') }}">DDJJ</a></li>
    <li class="breadcrumb-item active">{{ \Carbon\Carbon::parse($ddjj->fecha_inicio)->format('d/m/Y') }}</li>
@endsection

@section('content')

@php
    $estadoBadge = [
        'borrador'   => 'secondary',
        'presentada' => 'warning',
        'aprobada'   => 'success',
        'rechazada'  => 'danger',
    ][$ddjj->estado] ?? 'secondary';

    $dias = ['lunes','martes','miercoles','jueves','viernes'];
    $diasLabels = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
@endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--azul)">
            <i class="bi bi-file-earmark-text me-1"></i> Declaración Jurada
            <span class="badge bg-{{ $estadoBadge }} ms-1">{{ ucfirst($ddjj->estado) }}</span>
        </h5>
        <div class="small text-muted">
            {{ $ddjj->designacion->usuario->nombre_completo ?? '—' }} —
            {{ $ddjj->designacion->cargo->nombre ?? '—' }}
        </div>
        <div class="small text-muted">
            Período: {{ \Carbon\Carbon::parse($ddjj->fecha_inicio)->format('d/m/Y') }}
            — {{ \Carbon\Carbon::parse($ddjj->fecha_fin)->format('d/m/Y') }}
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        {{-- Presentar (si borrador) --}}
        @if($ddjj->estado === 'borrador')
            <form method="POST" action="{{ route('ddjj.presentar', $ddjj) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Presentar esta declaración jurada?')">
                    <i class="bi bi-send me-1"></i> Presentar
                </button>
            </form>
        @endif

        {{-- Aprobar / Rechazar (si presentada) --}}
        @if($ddjj->estado === 'presentada')
            <form method="POST" action="{{ route('ddjj.aprobar', $ddjj) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Aprobar esta declaración jurada?')">
                    <i class="bi bi-check-circle me-1"></i> Aprobar
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                <i class="bi bi-x-circle me-1"></i> Rechazar
            </button>
        @endif

        {{-- Eliminar (aprobada, solo con permiso) --}}
        @if($ddjj->estado === 'aprobada' && auth()->user()->permisos()->ddjj()->delete())
            <form method="POST" action="{{ route('ddjj.destroy', $ddjj) }}"
                  onsubmit="return confirm('¿Eliminar esta declaración jurada aprobada? Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
        @endif

        <a href="{{ route('ddjj.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

{{-- Grilla semanal --}}
<div class="card mb-3">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-calendar-week me-1"></i> Horario semanal
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px">Día</th>
                        <th>Hora entrada</th>
                        <th>Hora salida</th>
                        <th>Modalidad</th>
                        <th>Inst. externa</th>
                        <th>Dependencia</th>
                        <th>Edificio</th>
                        <th>Oficina/Aula</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dias as $i => $dia)
                        @php
                            $horariosDia = $ddjj->horarios->where('dia_semana', $dia)->values();
                        @endphp
                        @if($horariosDia->isEmpty())
                            <tr>
                                <td class="fw-semibold small">{{ $diasLabels[$i] }}</td>
                                <td colspan="7" class="text-muted small">Sin horario registrado</td>
                            </tr>
                        @else
                            @foreach($horariosDia as $j => $h)
                                <tr>
                                    @if($j === 0)
                                        <td class="fw-semibold small" rowspan="{{ $horariosDia->count() }}">{{ $diasLabels[$i] }}</td>
                                    @endif
                                    <td class="small">{{ $h->hora_entrada ? substr($h->hora_entrada, 0, 5) : '—' }}</td>
                                    <td class="small">{{ $h->hora_salida ? substr($h->hora_salida, 0, 5) : '—' }}</td>
                                    <td class="small">
                                        @if($h->modalidad)
                                            <span class="badge bg-info text-dark">{{ ucfirst($h->modalidad) }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="small">{{ $h->institucionExterna->nombre ?? '—' }}</td>
                                    <td class="small">{{ $h->dependencia->nombre ?? '—' }}</td>
                                    <td class="small">{{ $h->edificio->nombre ?? '—' }}</td>
                                    <td class="small">{{ $h->oficina->nombre ?? '—' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Observaciones --}}
@if($ddjj->observaciones)
    <div class="card">
        <div class="card-header" style="background:var(--azul);color:#fff">
            <i class="bi bi-chat-text me-1"></i> Observaciones
        </div>
        <div class="card-body">
            <p class="mb-0 small">{{ $ddjj->observaciones }}</p>
        </div>
    </div>
@endif

{{-- Modal Rechazar --}}
@if($ddjj->estado === 'presentada')
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ddjj.rechazar', $ddjj) }}">
                @csrf
                <div class="modal-header" style="background:var(--azul);color:#fff">
                    <h6 class="modal-title"><i class="bi bi-x-circle me-1"></i> Rechazar DDJJ</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold small">Motivo del rechazo</label>
                    <textarea name="observaciones_rechazo" rows="4" class="form-control form-control-sm" required
                              placeholder="Indique el motivo del rechazo…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Confirmar rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
