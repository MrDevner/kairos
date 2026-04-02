@extends('layouts.app')

@section('title', 'Banco de Horas — ' . ($usuario->nombre_completo ?? ''))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banco-horas.index') }}">Banco de Horas</a></li>
    <li class="breadcrumb-item active">{{ $usuario->nombre_completo ?? 'Usuario' }}</li>
@endsection

@section('content')
{{-- Header usuario --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div class="d-flex align-items-center gap-3">
        <div style="width:54px;height:54px;border-radius:50%;background:var(--azul);color:#fff;
                    display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.2rem">
            {{ strtoupper(substr($usuario->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($usuario->apellidos ?? '', 0, 1)) }}
        </div>
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--azul)">{{ $usuario->nombre_completo ?? '—' }}</h5>
            <div class="small text-muted">Banco de Horas</div>
        </div>
    </div>
    <a href="{{ route('banco-horas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

@foreach($bancos as $banco)
<div class="card mb-4">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-briefcase me-1"></i>
        {{ $banco->designacion->cargo->nombre ?? 'Sin cargo' }}
        @if($banco->designacion->institucion)
            <span class="badge bg-light text-dark ms-1">{{ $banco->designacion->institucion->nombre }}</span>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            {{-- Saldo --}}
            <div class="col-sm-4 text-center">
                @php $saldo = $banco->saldo_minutos ?? 0; @endphp
                <div class="fs-1 fw-bold {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $saldo >= 0 ? '+' : '' }}{{ $saldo }}
                </div>
                <div class="small text-muted">minutos de saldo</div>
                @php
                    $horas = intdiv(abs($saldo), 60);
                    $mins  = abs($saldo) % 60;
                @endphp
                <div class="small text-muted">({{ $saldo >= 0 ? '+' : '-' }}{{ $horas }}h {{ $mins }}m)</div>
            </div>

            {{-- Permisos --}}
            <div class="col-sm-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    @if($banco->autorizado_acumular)
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    @else
                        <i class="bi bi-x-circle text-muted fs-5"></i>
                    @endif
                    <span class="small">Autorizado acumular horas</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($banco->autorizado_negativo)
                        <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                    @else
                        <i class="bi bi-x-circle text-muted fs-5"></i>
                    @endif
                    <span class="small">Puede tener saldo negativo</span>
                </div>
            </div>
        </div>

        {{-- Movimientos --}}
        <h6 class="fw-semibold mb-2 small" style="color:var(--azul)">Últimos 50 movimientos</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Minutos</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @php $movimientos = $banco->movimientos->take(50); @endphp
                    @forelse($movimientos as $mov)
                        <tr>
                            <td class="small">{{ $mov->fecha ? \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') : '—' }}</td>
                            <td class="text-center">
                                @php
                                    $tipoBadge = [
                                        'acreditacion' => 'success',
                                        'debito'       => 'danger',
                                        'ajuste'       => 'info',
                                        'vencimiento'  => 'secondary',
                                    ][$mov->tipo] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $tipoBadge }}">{{ ucfirst($mov->tipo) }}</span>
                            </td>
                            <td class="text-center fw-semibold small">
                                <span class="{{ $mov->minutos >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $mov->minutos >= 0 ? '+' : '' }}{{ $mov->minutos }}
                                </span>
                            </td>
                            <td class="small">{{ $mov->motivo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3 small">Sin movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Ajuste manual --}}
        <div class="mt-4 border-top pt-3">
            <h6 class="fw-semibold small mb-2" style="color:var(--azul)">
                <i class="bi bi-pencil-square me-1"></i> Ajuste manual
            </h6>
            <form method="POST" action="{{ route('banco-horas.ajuste', ['usuario' => $usuario, 'banco' => $banco]) }}"
                  class="row g-2 align-items-end">
                @csrf
                <div class="col-sm-3">
                    <label class="form-label form-label-sm small mb-0">Minutos <span class="text-danger">*</span></label>
                    <input type="number" name="minutos" class="form-control form-control-sm @error('minutos_'.$banco->id) is-invalid @enderror"
                           placeholder="Ej: 60 o -30" required>
                    <div class="form-text" style="font-size:.7rem">Positivo suma, negativo resta.</div>
                </div>
                <div class="col-sm-5">
                    <label class="form-label form-label-sm small mb-0">Motivo <span class="text-danger">*</span></label>
                    <input type="text" name="motivo" class="form-control form-control-sm @error('motivo_'.$banco->id) is-invalid @enderror"
                           placeholder="Motivo del ajuste…" required maxlength="200">
                </div>
                <div class="col-sm-auto">
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff"
                            onclick="return confirm('¿Confirmar ajuste manual de horas?')">
                        <i class="bi bi-check-lg me-1"></i> Aplicar ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@if($bancos->isEmpty())
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Este usuario no tiene registros en el banco de horas.
    </div>
@endif
@endsection
