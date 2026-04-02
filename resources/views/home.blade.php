@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
@php $user = auth()->user(); $instId = (int) session('institucion_activa_id', 0); @endphp

{{-- ══ ADMINISTRADOR GENERAL ═══════════════════════════════════════════════ --}}
@if($user->hasRole('Administrador General'))
    <h5 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2" style="color:var(--azul)"></i>Dashboard — Administrador General</h5>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-building fs-2" style="color:var(--azul)"></i>
                    <div class="fs-3 fw-bold mt-1">{{ $stats['instituciones'] ?? 0 }}</div>
                    <div class="text-muted small">Instituciones</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-people fs-2" style="color:var(--celeste)"></i>
                    <div class="fs-3 fw-bold mt-1">{{ $stats['usuarios'] ?? 0 }}</div>
                    <div class="text-muted small">Usuarios activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-hdd-network fs-2" style="color:var(--dorado)"></i>
                    <div class="fs-3 fw-bold mt-1">{{ $stats['dispositivos'] ?? 0 }}</div>
                    <div class="text-muted small">Dispositivos activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                    <div class="fs-3 fw-bold mt-1 text-danger">{{ $stats['errores_hoy'] ?? 0 }}</div>
                    <div class="text-muted small">Errores hoy</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-activity me-2"></i>Actividad del sistema — últimos 30 días</div>
                <div class="card-body">
                    <canvas id="chartActividad" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-bg-danger border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Errores críticos</div>
                <div class="card-body p-0">
                    @forelse($erroresCriticos ?? [] as $err)
                        <div class="px-3 py-2 border-bottom small">{{ $err }}</div>
                    @empty
                        <div class="px-3 py-3 text-muted small text-center">Sin errores críticos</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

{{-- ══ JEFE DE PERSONAL / DEPTO PERSONAL ═══════════════════════════════════ --}}
@elseif($user->tieneRolEnInstitucion('Jefe de Personal', $instId) || $user->tieneRolEnInstitucion('Departamento Personal', $instId))
    <h5 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2" style="color:var(--azul)"></i>Dashboard — {{ now()->isoFormat('dddd D [de] MMMM YYYY') }}</h5>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center h-100" style="border-top:3px solid #198754">
                <div class="card-body py-3">
                    <i class="bi bi-person-check fs-2 text-success"></i>
                    <div class="fs-3 fw-bold mt-1 text-success">{{ $stats['presentes'] ?? 0 }}</div>
                    <div class="text-muted small">Presentes hoy</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100" style="border-top:3px solid #dc3545">
                <div class="card-body py-3">
                    <i class="bi bi-person-x fs-2 text-danger"></i>
                    <div class="fs-3 fw-bold mt-1 text-danger">{{ $stats['ausentes'] ?? 0 }}</div>
                    <div class="text-muted small">Ausentes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100" style="border-top:3px solid #ffc107">
                <div class="card-body py-3">
                    <i class="bi bi-clock-history fs-2 text-warning"></i>
                    <div class="fs-3 fw-bold mt-1 text-warning">{{ $stats['tardanzas'] ?? 0 }}</div>
                    <div class="text-muted small">Tardanzas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100" style="border-top:3px solid #dc3545">
                <div class="card-body py-3">
                    <i class="bi bi-exclamation-circle fs-2 text-danger"></i>
                    <div class="fs-3 fw-bold mt-1 text-danger">{{ $stats['sin_justificar'] ?? 0 }}</div>
                    <div class="text-muted small">Sin justificar</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-bar-chart-fill me-2"></i>Asistencia semanal</div>
                <div class="card-body"><canvas id="chartAsistencia" height="110"></canvas></div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header text-bg-danger border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Atención urgente hoy</div>
                <div class="card-body p-0" style="max-height:260px;overflow-y:auto">
                    @forelse($urgentes ?? [] as $item)
                        <div class="px-3 py-2 border-bottom small d-flex justify-content-between">
                            <span>{{ $item->usuario->nombre_completo }}</span>
                            <span class="text-muted">{{ $item->detalle }}</span>
                        </div>
                    @empty
                        <div class="px-3 py-3 text-muted small text-center"><i class="bi bi-check-circle text-success"></i> Sin novedades urgentes</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

{{-- ══ DIRECTOR ADMINISTRATIVO / AUDITOR ════════════════════════════════════ --}}
@elseif($user->tieneRolEnInstitucion('Director Administrativo', $instId) || $user->tieneRolEnInstitucion('Auditor', $instId))
    <h5 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2" style="color:var(--azul)"></i>Dashboard</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card text-center"><div class="card-body py-4"><i class="bi bi-people fs-1" style="color:var(--azul)"></i><div class="fs-2 fw-bold mt-1">{{ $stats['personal'] ?? 0 }}</div><div class="text-muted">Personal total</div></div></div></div>
        <div class="col-md-4"><div class="card text-center"><div class="card-body py-4"><i class="bi bi-person-check fs-1 text-success"></i><div class="fs-2 fw-bold mt-1 text-success">{{ $stats['presentes'] ?? 0 }}</div><div class="text-muted">Presentes hoy</div></div></div></div>
        <div class="col-md-4"><div class="card text-center"><div class="card-body py-4"><i class="bi bi-person-x fs-1 text-danger"></i><div class="fs-2 fw-bold mt-1 text-danger">{{ $stats['ausentes'] ?? 0 }}</div><div class="text-muted">Ausentes hoy</div></div></div></div>
    </div>

{{-- ══ USUARIO COMÚN ════════════════════════════════════════════════════════ --}}
@else
    <h5 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2" style="color:var(--azul)"></i>Mi resumen de hoy — {{ now()->isoFormat('dddd D [de] MMMM') }}</h5>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-box-arrow-in-right fs-2 text-success"></i>
                    <div class="fw-bold mt-1">{{ $marcaHoy?->hora_entrada ?? '—' }}</div>
                    <div class="text-muted small">Entrada</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-box-arrow-right fs-2 text-secondary"></i>
                    <div class="fw-bold mt-1">{{ $marcaHoy?->hora_salida ?? '—' }}</div>
                    <div class="text-muted small">Salida</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <i class="bi bi-bank fs-2" style="color:var(--azul)"></i>
                    @php $signo = ($saldoBanco ?? 0) >= 0 ? '+' : ''; @endphp
                    <div class="fw-bold mt-1 {{ ($saldoBanco ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $signo }}{{ round(($saldoBanco ?? 0) / 60, 1) }}h
                    </div>
                    <div class="text-muted small">Banco de horas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    @php
                        $tipoMarca = $marcaHoy?->tipo ?? 'sin_marca';
                        $colorEstado = match($tipoMarca) {
                            'normal'   => ['text-success', 'bi-check-circle-fill', 'Presente'],
                            'tardanza' => ['text-warning', 'bi-clock-history', 'Tardanza'],
                            'ausencia' => ['text-danger',  'bi-x-circle-fill', 'Ausente'],
                            'licencia' => ['text-info',    'bi-calendar-check', 'Licencia'],
                            default    => ['text-muted',   'bi-dash-circle', 'Sin marca'],
                        };
                    @endphp
                    <i class="bi {{ $colorEstado[1] }} fs-2 {{ $colorEstado[0] }}"></i>
                    <div class="fw-bold mt-1 {{ $colorEstado[0] }}">{{ $colorEstado[2] }}</div>
                    <div class="text-muted small">Estado hoy</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-file-text me-2"></i>Mi DDJJ vigente</div>
                <div class="card-body">
                    @if($ddjjVigente ?? null)
                        <p class="mb-1 small text-muted">Vigente desde {{ $ddjjVigente->fecha_inicio->format('d/m/Y') }}</p>
                        <table class="table table-sm table-borderless mb-0">
                            @foreach($ddjjVigente->horarios as $h)
                                <tr>
                                    <td class="text-capitalize fw-semibold" style="width:110px">{{ $h->dia_semana }}</td>
                                    <td>{{ substr($h->hora_entrada,0,5) }} — {{ substr($h->hora_salida,0,5) }}</td>
                                    <td><span class="badge bg-secondary">{{ $h->modalidad }}</span></td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p class="text-muted small mb-0">No tiene una declaración jurada aprobada.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-megaphone me-2"></i>Últimos avisos / licencias</div>
                <div class="card-body p-0">
                    @forelse($ultimosMovimientos ?? [] as $mov)
                        <div class="px-3 py-2 border-bottom small d-flex justify-content-between">
                            <span>{{ $mov['tipo'] }}</span>
                            <span class="text-muted">{{ $mov['fecha'] }}</span>
                        </div>
                    @empty
                        <div class="px-3 py-3 text-muted small text-center">Sin novedades recientes</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
@if($user->hasRole('Administrador General'))
// Gráfico actividad 30 días
const ctxAct = document.getElementById('chartActividad');
if (ctxAct) {
    new Chart(ctxAct, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Marcas procesadas',
                data: {!! json_encode($chartData ?? []) !!},
                borderColor: '#1B4F72',
                backgroundColor: 'rgba(117,170,219,.2)',
                tension: .3,
                fill: true,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
}
@elseif($user->tieneRolEnInstitucion('Jefe de Personal', $instId) || $user->tieneRolEnInstitucion('Departamento Personal', $instId))
// Gráfico asistencia semanal
const ctxAsis = document.getElementById('chartAsistencia');
if (ctxAsis) {
    new Chart(ctxAsis, {
        type: 'bar',
        data: {
            labels: {!! json_encode($semanaLabels ?? ['Lun','Mar','Mié','Jue','Vie']) !!},
            datasets: [
                { label: 'Presentes',  data: {!! json_encode($semanaPresentes ?? [0,0,0,0,0]) !!}, backgroundColor: '#198754' },
                { label: 'Ausentes',   data: {!! json_encode($semanaAusentes  ?? [0,0,0,0,0]) !!}, backgroundColor: '#dc3545' },
                { label: 'Tardanzas',  data: {!! json_encode($semanaTardanzas ?? [0,0,0,0,0]) !!}, backgroundColor: '#ffc107' },
            ]
        },
        options: { plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: false }, y: { beginAtZero: true } } }
    });
}
@endif
</script>
@endpush
