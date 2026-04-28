@extends('layouts.app')

@section('title', 'Banco de Horas')

@section('breadcrumb')
    <li class="breadcrumb-item active">Banco de Horas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-hourglass-split me-1"></i> Banco de Horas
    </h5>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('banco-horas.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-5">
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="form-control form-control-sm" placeholder="Buscar por usuario…">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('banco-horas.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Saldos
        <span class="badge bg-light text-dark ms-auto">{{ $bancos->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Cargo / Designación</th>
                        <th class="text-center">Saldo (min)</th>
                        <th class="text-center">Acumular</th>
                        <th class="text-center">Saldo negativo</th>
                        <th class="text-center" style="width:90px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bancos as $banco)
                        <tr>
                            <td>
                                <a href="{{ route('usuarios.show', $banco->usuario) }}" class="text-decoration-none">
                                    {{ $banco->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">
                                {{ $banco->designacion->cargo->nombre ?? '—' }}
                                @if($banco->designacion->dependencia)
                                    <div class="text-muted" style="font-size:.72rem">{{ $banco->designacion->dependencia->nombre }}</div>
                                @endif
                            </td>
                            <td class="text-center fw-bold">
                                @php $saldo = $banco->saldo_minutos ?? 0; @endphp
                                <span class="{{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $saldo >= 0 ? '+' : '' }}{{ $saldo }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($banco->autorizado_acumular)
                                    <i class="bi bi-check-circle-fill text-success" title="Autorizado para acumular"></i>
                                @else
                                    <i class="bi bi-x-circle text-muted" title="No autorizado"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($banco->autorizado_negativo)
                                    <i class="bi bi-check-circle-fill text-warning" title="Puede tener saldo negativo"></i>
                                @else
                                    <i class="bi bi-x-circle text-muted" title="No autorizado negativo"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('banco-horas.show', $banco->usuario) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($bancos->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $bancos->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
