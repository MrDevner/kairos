@extends('layouts.app')

{{--
    Dashboard según rol cuando no hay institución activa en sesión.
    $tipo: 'personal' | 'director' | 'usuario'
--}}

@section('title', 'Inicio')

@section('page-title')
    @if($tipo === 'personal')
        Dashboard de personal
    @elseif($tipo === 'director')
        Dashboard
    @else
        Mi resumen de hoy
    @endif
@endsection

@section('page-subtitle')
    {{ $hoy->isoFormat('dddd D [de] MMMM [de] YYYY') }}
@endsection

@section('breadcrumb')
    <li class="active">Inicio</li>
@endsection

@section('content')

@if($tipo === 'personal')
    {{-- ══ JEFE DE PERSONAL / DEPTO PERSONAL ══════════════════════════════ --}}
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi teal">
                <span class="k2-kpi-icon"><i class="bi bi-person-check"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Presentes hoy</span>
                    <span class="k2-kpi-value d-block">{{ $stats['presentes'] ?? 0 }}</span>
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi danger">
                <span class="k2-kpi-icon"><i class="bi bi-person-x"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Ausentes</span>
                    <span class="k2-kpi-value d-block">{{ $stats['ausentes'] ?? 0 }}</span>
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi amber">
                <span class="k2-kpi-icon"><i class="bi bi-clock-history"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Tardanzas</span>
                    <span class="k2-kpi-value d-block">{{ $stats['tardanzas'] ?? 0 }}</span>
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi danger">
                <span class="k2-kpi-icon"><i class="bi bi-exclamation-circle"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Sin justificar</span>
                    <span class="k2-kpi-value d-block">{{ $stats['sin_justificar'] ?? 0 }}</span>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-lg-7">
            <div class="k2-card">
                <div class="k2-card-head">
                    <div>
                        <h2 class="k2-card-title"><i class="bi bi-bar-chart-fill"></i> Asistencia semanal</h2>
                        <div class="k2-card-sub">Últimos cinco días hábiles</div>
                    </div>
                    <div class="k2-card-tools">
                        <span class="k2-badge teal">Presentes</span>
                        <span class="k2-badge danger">Ausentes</span>
                        <span class="k2-badge amber">Tardanzas</span>
                    </div>
                </div>
                <div class="k2-card-body">
                    <div class="k2-chart" style="height:280px"><canvas id="k2ChartAsistencia"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="k2-card">
                <div class="k2-card-head">
                    <div>
                        <h2 class="k2-card-title"><i class="bi bi-exclamation-triangle"></i> Atención urgente hoy</h2>
                        <div class="k2-card-sub">Novedades que requieren intervención</div>
                    </div>
                </div>
                <div class="k2-card-body flush" style="max-height:320px;overflow-y:auto">
                    @forelse($urgentes ?? [] as $item)
                        <div class="k2-list-item">
                            <span class="k2-initials danger"><i class="bi bi-exclamation-lg"></i></span>
                            <span class="k2-list-main">
                                <span class="k2-list-title">{{ $item->usuario->nombre_completo ?? '—' }}</span>
                                <span class="k2-list-meta">{{ $item->detalle }}</span>
                            </span>
                        </div>
                    @empty
                        <div class="k2-empty"><i class="bi bi-check-circle"></i>Sin novedades urgentes</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@elseif($tipo === 'director')
    {{-- ══ DIRECTOR ADMINISTRATIVO / AUDITOR ══════════════════════════════ --}}
    <div class="row g-3">
        <div class="col-md-4">
            <div class="k2-card k2-kpi">
                <span class="k2-kpi-icon"><i class="bi bi-people"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Personal total</span>
                    <span class="k2-kpi-value d-block">{{ $stats['personal'] ?? 0 }}</span>
                    <span class="k2-kpi-meta"><span>designaciones vigentes</span></span>
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="k2-card k2-kpi teal">
                <span class="k2-kpi-icon"><i class="bi bi-person-check"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Presentes hoy</span>
                    <span class="k2-kpi-value d-block">{{ $stats['presentes'] ?? 0 }}</span>
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="k2-card k2-kpi danger">
                <span class="k2-kpi-icon"><i class="bi bi-person-x"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Ausentes hoy</span>
                    <span class="k2-kpi-value d-block">{{ $stats['ausentes'] ?? 0 }}</span>
                </span>
            </div>
        </div>
    </div>

@else
    {{-- ══ USUARIO COMÚN ══════════════════════════════════════════════════ --}}
    @php
        $tipoMarca = $marcaHoy?->tipo ?? 'sin_marca';
        [$estadoVar, $estadoIcono, $estadoLabel] = match ($tipoMarca) {
            'normal'   => ['teal',    'bi-check-circle-fill', 'Presente'],
            'tardanza' => ['amber',   'bi-clock-history',     'Tardanza'],
            'ausencia' => ['danger',  'bi-x-circle-fill',     'Ausente'],
            'licencia' => ['primary', 'bi-calendar-check',    'Licencia'],
            default    => ['',        'bi-dash-circle',       'Sin marca'],
        };
        $saldo = $saldoBanco ?? 0;
    @endphp

    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi teal">
                <span class="k2-kpi-icon"><i class="bi bi-box-arrow-in-right"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Entrada</span>
                    <span class="k2-kpi-value d-block">{{ $marcaHoy?->hora_entrada ? substr($marcaHoy->hora_entrada, 0, 5) : '—' }}</span>
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi">
                <span class="k2-kpi-icon"><i class="bi bi-box-arrow-right"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Salida</span>
                    <span class="k2-kpi-value d-block">{{ $marcaHoy?->hora_salida ? substr($marcaHoy->hora_salida, 0, 5) : '—' }}</span>
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi {{ $saldo < 0 ? 'danger' : 'teal' }}">
                <span class="k2-kpi-icon"><i class="bi bi-bank"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Banco de horas</span>
                    <span class="k2-kpi-value d-block">{{ $saldo >= 0 ? '+' : '' }}{{ round($saldo / 60, 1) }} h</span>
                    <span class="k2-kpi-meta"><span>{{ $saldo >= 0 ? 'a favor' : 'en contra' }}</span></span>
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="k2-card k2-kpi {{ $estadoVar }}">
                <span class="k2-kpi-icon"><i class="bi {{ $estadoIcono }}"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Estado hoy</span>
                    <span class="k2-kpi-value sm d-block">{{ $estadoLabel }}</span>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-md-6">
            <div class="k2-card">
                <div class="k2-card-head">
                    <div>
                        <h2 class="k2-card-title"><i class="bi bi-file-earmark-text"></i> Mi DDJJ vigente</h2>
                        @if($ddjjVigente ?? null)
                            <div class="k2-card-sub">Vigente desde {{ $ddjjVigente->fecha_inicio->format('d/m/Y') }}</div>
                        @endif
                    </div>
                    <div class="k2-card-tools">
                        <a href="{{ route('ddjj.index') }}" class="k2-btn k2-btn-ghost k2-btn-sm">Ver DDJJ <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="k2-card-body flush">
                    @if($ddjjVigente ?? null)
                        <table class="k2-table">
                            <thead><tr><th>Día</th><th>Horario</th><th>Modalidad</th></tr></thead>
                            <tbody>
                                @foreach($ddjjVigente->horarios as $h)
                                    <tr>
                                        <td class="text-capitalize fw-semibold">{{ $h->dia_semana }}</td>
                                        <td>{{ substr($h->hora_entrada, 0, 5) }} — {{ substr($h->hora_salida, 0, 5) }}</td>
                                        <td><span class="k2-badge neutral">{{ $h->modalidad }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="k2-empty"><i class="bi bi-file-earmark-x"></i>No tiene una declaración jurada aprobada</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="k2-card">
                <div class="k2-card-head">
                    <div>
                        <h2 class="k2-card-title"><i class="bi bi-megaphone"></i> Últimos avisos y DDJJ</h2>
                        <div class="k2-card-sub">Movimientos recientes</div>
                    </div>
                </div>
                <div class="k2-card-body flush">
                    @forelse($ultimosMovimientos ?? [] as $mov)
                        <div class="k2-list-item">
                            <span class="k2-initials"><i class="bi bi-arrow-right-short"></i></span>
                            <span class="k2-list-main"><span class="k2-list-title">{{ $mov['tipo'] }}</span></span>
                            <span class="k2-list-side">{{ $mov['fecha'] }}</span>
                        </div>
                    @empty
                        <div class="k2-empty"><i class="bi bi-inbox"></i>Sin novedades recientes</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@if($tipo === 'personal')
@push('scripts')
<script>
(function () {
    'use strict';
    var el = document.getElementById('k2ChartAsistencia');
    if (!el || !window.Chart) return;

    var tick = k2.cssVar('--k2-ink-3') || '#8B93A7';
    var grid = k2.isDark() ? 'rgba(255,255,255,.06)' : 'rgba(43,45,66,.07)';

    var chart = new Chart(el, {
        type: 'bar',
        data: {
            labels: {!! json_encode($semanaLabels ?? ['Lun','Mar','Mié','Jue','Vie']) !!},
            datasets: [
                { label: 'Presentes', data: {!! json_encode($semanaPresentes ?? [0,0,0,0,0]) !!}, backgroundColor: '#4CAF93', borderRadius: 6, maxBarThickness: 28 },
                { label: 'Ausentes',  data: {!! json_encode($semanaAusentes  ?? [0,0,0,0,0]) !!}, backgroundColor: '#D64550', borderRadius: 6, maxBarThickness: 28 },
                { label: 'Tardanzas', data: {!! json_encode($semanaTardanzas ?? [0,0,0,0,0]) !!}, backgroundColor: '#F2A65A', borderRadius: 6, maxBarThickness: 28 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: tick }, border: { display: false } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { color: tick, precision: 0 }, border: { display: false } },
            }
        }
    });

    window.addEventListener('k2:theme', function () {
        chart.options.scales.y.grid.color = k2.isDark() ? 'rgba(255,255,255,.06)' : 'rgba(43,45,66,.07)';
        chart.options.scales.x.ticks.color = chart.options.scales.y.ticks.color = k2.cssVar('--k2-ink-3');
        chart.update();
    });
})();
</script>
@endpush
@endif
