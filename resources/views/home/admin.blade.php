@extends('layouts.app')

@section('title', 'Inicio')
@section('page-title', 'Panel de administración')
@section('page-subtitle')
    Visión general del sistema · {{ $hoy->isoFormat('dddd D [de] MMMM [de] YYYY') }}
@endsection

@section('breadcrumb')
    <li class="active">Inicio</li>
@endsection

@section('page-actions')
    @if($instActiva)
        <a href="{{ route('home.institucion') }}" class="k2-btn" title="Dashboard de {{ $instActiva->nombre }}">
            <i class="bi bi-building"></i> Dashboard de {{ $instActiva->sigla ?: \Illuminate\Support\Str::limit($instActiva->nombre, 22) }}
        </a>
    @endif
    <a href="{{ route('usuarios.create') }}" class="k2-btn">
        <i class="bi bi-person-plus"></i> Nuevo usuario
    </a>
    <a href="{{ route('instituciones.create') }}" class="k2-btn k2-btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva institución
    </a>
@endsection

@section('content')
@php
    $iniciales = fn ($u) => strtoupper(substr($u?->nombres ?? '', 0, 1) . substr($u?->apellidos ?? '', 0, 1)) ?: '—';

    $ticketEstado = [
        'abierto'    => ['Abierto',    'primary'],
        'en_proceso' => ['En proceso', 'amber'],
        'resuelto'   => ['Resuelto',   'teal'],
        'cerrado'    => ['Cerrado',    'neutral'],
    ];
    $ticketPrioridad = [
        'urgente' => ['Urgente', 'danger'],
        'alta'    => ['Alta',    'amber'],
        'media'   => ['Media',   'primary'],
        'baja'    => ['Baja',    'neutral'],
    ];
    $errorEstado = [
        'abierto'     => ['Abierto',     'danger'],
        'en_revision' => ['En revisión', 'amber'],
        'mitigado'    => ['Mitigado',    'teal'],
        'solucionado' => ['Solucionado', 'neutral'],
    ];
    $eventoLog = [
        'created' => ['creó',       'teal'],
        'updated' => ['actualizó',  'primary'],
        'deleted' => ['eliminó',    'danger'],
    ];
@endphp

{{-- ══ INDICADORES PRINCIPALES ═════════════════════════════════════════════ --}}
<div class="row g-3">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('instituciones.index') }}" class="k2-card k2-kpi">
            <span class="k2-kpi-icon"><i class="bi bi-building"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Instituciones activas</span>
                <span class="k2-kpi-value d-block">{{ number_format($kpis['instituciones'], 0, ',', '.') }}</span>
                <span class="k2-kpi-meta">
                    <span>{{ number_format($kpis['instituciones_total'], 0, ',', '.') }} registradas en total</span>
                </span>
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('usuarios.index', ['todos' => 1]) }}" class="k2-card k2-kpi teal">
            <span class="k2-kpi-icon"><i class="bi bi-people"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Usuarios activos</span>
                <span class="k2-kpi-value d-block">{{ number_format($kpis['usuarios'], 0, ',', '.') }}</span>
                <span class="k2-kpi-meta">
                    @if($kpis['usuarios_nuevos_30d'] > 0)
                        <span class="k2-delta up"><i class="bi bi-arrow-up-short"></i>{{ $kpis['usuarios_nuevos_30d'] }}</span>
                        <span>nuevos en 30 días</span>
                    @else
                        <span class="k2-delta flat">Sin altas</span>
                        <span>en los últimos 30 días</span>
                    @endif
                </span>
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('designaciones.index') }}" class="k2-card k2-kpi amber">
            <span class="k2-kpi-icon"><i class="bi bi-person-vcard"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Personal vigente</span>
                <span class="k2-kpi-value d-block">{{ number_format($kpis['personal_vigente'], 0, ',', '.') }}</span>
                <span class="k2-kpi-meta">
                    <span>designaciones activas hoy</span>
                </span>
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('marcas.index') }}" class="k2-card k2-kpi">
            <span class="k2-kpi-icon"><i class="bi bi-fingerprint"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Marcas de hoy</span>
                <span class="k2-kpi-value d-block">{{ number_format($kpis['marcas_hoy'], 0, ',', '.') }}</span>
                <span class="k2-kpi-meta">
                    @if($kpis['marcas_delta_pct'] === null)
                        <span class="k2-delta flat">Sin referencia</span>
                        <span>de ayer</span>
                    @elseif($kpis['marcas_delta_pct'] > 0)
                        <span class="k2-delta up"><i class="bi bi-arrow-up-short"></i>{{ $kpis['marcas_delta_pct'] }}%</span>
                        <span>respecto de ayer</span>
                    @elseif($kpis['marcas_delta_pct'] < 0)
                        <span class="k2-delta down"><i class="bi bi-arrow-down-short"></i>{{ abs($kpis['marcas_delta_pct']) }}%</span>
                        <span>respecto de ayer</span>
                    @else
                        <span class="k2-delta flat">Igual</span>
                        <span>que ayer</span>
                    @endif
                </span>
            </span>
        </a>
    </div>
</div>

{{-- ══ ACTIVIDAD + ATENCIÓN ════════════════════════════════════════════════ --}}
<div class="row g-3 mt-0">
    <div class="col-xl-8">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-activity"></i> Actividad de marcas</h2>
                    <div class="k2-card-sub">Últimos 30 días · marcas registradas y jornadas computadas con error</div>
                </div>
                <div class="k2-card-tools">
                    <span class="k2-badge primary"><span class="k2-pulse"></span> Marcas</span>
                    <span class="k2-badge danger">Con error</span>
                </div>
            </div>
            <div class="k2-card-body">
                <div class="k2-chart" style="height:300px">
                    <canvas id="k2ChartMarcas"></canvas>
                </div>
            </div>
            <div class="k2-card-foot d-flex flex-wrap gap-3">
                <span><strong>{{ number_format(array_sum($serie['marcas']), 0, ',', '.') }}</strong> marcas en el período</span>
                <span><strong>{{ number_format(array_sum($serie['errores']), 0, ',', '.') }}</strong> jornadas con error</span>
                <span class="ms-auto">{{ number_format($kpis['marcas_sin_procesar'], 0, ',', '.') }} marcas sin procesar</span>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-exclamation-diamond"></i> Requieren atención</h2>
                    <div class="k2-card-sub">Pendientes a nivel global</div>
                </div>
            </div>
            <div class="k2-card-body flush">
                <ul class="k2-list">
                    <li>
                        <a href="{{ route('licencias.index', ['estado' => 'pendiente']) }}" class="k2-list-item">
                            <span class="k2-initials amber"><i class="bi bi-calendar-check"></i></span>
                            <span class="k2-list-main">
                                <span class="k2-list-title">Licencias pendientes</span>
                                <span class="k2-list-meta">A la espera de aprobación</span>
                            </span>
                            <span class="k2-attn-count {{ $atencion['licencias_pendientes'] === 0 ? 'zero' : '' }}">{{ $atencion['licencias_pendientes'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tickets.index') }}" class="k2-list-item">
                            <span class="k2-initials"><i class="bi bi-life-preserver"></i></span>
                            <span class="k2-list-main">
                                <span class="k2-list-title">Tickets abiertos</span>
                                <span class="k2-list-meta">
                                    @if($atencion['tickets_urgentes'] > 0)
                                        <span class="k2-badge danger">{{ $atencion['tickets_urgentes'] }} urgentes</span>
                                    @else
                                        Sin tickets urgentes
                                    @endif
                                </span>
                            </span>
                            <span class="k2-attn-count {{ $atencion['tickets_abiertos'] === 0 ? 'zero' : '' }}">{{ $atencion['tickets_abiertos'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.errores-servidor.index') }}" class="k2-list-item">
                            <span class="k2-initials danger"><i class="bi bi-bug"></i></span>
                            <span class="k2-list-main">
                                <span class="k2-list-title">Errores de servidor</span>
                                <span class="k2-list-meta">Abiertos o en revisión</span>
                            </span>
                            <span class="k2-attn-count {{ $atencion['errores_activos'] === 0 ? 'zero' : '' }}">{{ $atencion['errores_activos'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('marcas.index') }}" class="k2-list-item">
                            <span class="k2-initials amber"><i class="bi bi-exclamation-triangle"></i></span>
                            <span class="k2-list-main">
                                <span class="k2-list-title">Jornadas con error hoy</span>
                                <span class="k2-list-meta">Marcas computadas inconsistentes</span>
                            </span>
                            <span class="k2-attn-count {{ $atencion['marcas_error_hoy'] === 0 ? 'zero' : '' }}">{{ $atencion['marcas_error_hoy'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dispositivos.index') }}" class="k2-list-item">
                            <span class="k2-initials teal"><i class="bi bi-hdd-network"></i></span>
                            <span class="k2-list-main">
                                <span class="k2-list-title">Dispositivos inactivos</span>
                                <span class="k2-list-meta">{{ $kpis['dispositivos'] }} de {{ $kpis['dispositivos_total'] }} operativos</span>
                            </span>
                            <span class="k2-attn-count {{ $atencion['dispositivos_inactivos'] === 0 ? 'zero' : '' }}">{{ $atencion['dispositivos_inactivos'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="k2-card-foot">
                <i class="bi bi-clock me-1"></i> Actualizado a las {{ now()->format('H:i') }}
            </div>
        </div>
    </div>
</div>

{{-- ══ LICENCIAS + TICKETS ═════════════════════════════════════════════════ --}}
<div class="k2-section-title">Operación diaria</div>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-hourglass-split"></i> Licencias pendientes</h2>
                    <div class="k2-card-sub">Próximas a iniciar primero</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('licencias.index', ['estado' => 'pendiente']) }}" class="k2-btn k2-btn-ghost k2-btn-sm">Ver todas <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush">
                @if($licenciasPendientes->isEmpty())
                    <div class="k2-empty"><i class="bi bi-check2-circle"></i>No hay licencias pendientes de aprobación</div>
                @else
                    <div class="k2-table-wrap">
                        <table class="k2-table">
                            <thead>
                                <tr>
                                    <th>Persona</th>
                                    <th>Tipo</th>
                                    <th>Período</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($licenciasPendientes as $lic)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="k2-initials">{{ $iniciales($lic->usuario) }}</span>
                                                <div class="min-w-0">
                                                    <div class="fw-semibold text-truncate" style="max-width:200px">{{ $lic->usuario?->nombre_completo ?? '—' }}</div>
                                                    <div class="muted">{{ $lic->designacion?->institucion?->sigla ?? $lic->designacion?->institucion?->nombre ?? 'Sin designación' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $lic->tipoLicencia?->nombre ?? '—' }}</td>
                                        <td class="text-nowrap">
                                            {{ $lic->fecha_inicio?->format('d/m/Y') }}
                                            <span class="muted">→ {{ $lic->fecha_fin?->format('d/m/Y') ?? '∞' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('licencias.show', $lic) }}" class="k2-btn k2-btn-sm" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($atencion['licencias_pendientes'] > $licenciasPendientes->count())
                <div class="k2-card-foot">Mostrando {{ $licenciasPendientes->count() }} de {{ $atencion['licencias_pendientes'] }} pendientes</div>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-life-preserver"></i> Tickets abiertos</h2>
                    <div class="k2-card-sub">Ordenados por estado y prioridad</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('tickets.index') }}" class="k2-btn k2-btn-ghost k2-btn-sm">Ver todos <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush">
                @if($ticketsAbiertos->isEmpty())
                    <div class="k2-empty"><i class="bi bi-check2-circle"></i>No hay tickets abiertos</div>
                @else
                    <ul class="k2-list">
                        @foreach($ticketsAbiertos as $t)
                            @php
                                [$estLabel, $estVar] = $ticketEstado[$t->estado] ?? [$t->estado, 'neutral'];
                                [$priLabel, $priVar] = $ticketPrioridad[$t->prioridad] ?? [$t->prioridad, 'neutral'];
                            @endphp
                            <li>
                                <a href="{{ route('tickets.show', $t) }}" class="k2-list-item">
                                    <span class="k2-initials {{ $priVar === 'neutral' ? '' : $priVar }}">#{{ $t->id }}</span>
                                    <span class="k2-list-main">
                                        <span class="k2-list-title">{{ $t->titulo }}</span>
                                        <span class="k2-list-meta">
                                            {{ $t->creador?->nombre_completo ?? '—' }}
                                            · {{ $t->asignadoA ? 'Asignado a ' . $t->asignadoA->nombre_completo : 'Sin asignar' }}
                                            · {{ $t->created_at?->diffForHumans() }}
                                        </span>
                                    </span>
                                    <span class="k2-list-side d-flex flex-column align-items-end gap-1">
                                        <span class="k2-badge {{ $priVar }}">{{ $priLabel }}</span>
                                        <span class="k2-badge {{ $estVar }}">{{ $estLabel }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if($atencion['tickets_abiertos'] > $ticketsAbiertos->count())
                <div class="k2-card-foot">Mostrando {{ $ticketsAbiertos->count() }} de {{ $atencion['tickets_abiertos'] }} abiertos</div>
            @endif
        </div>
    </div>
</div>

{{-- ══ ERRORES + ACTIVIDAD ═════════════════════════════════════════════════ --}}
<div class="k2-section-title">Salud del sistema</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-bug"></i> Errores de servidor</h2>
                    <div class="k2-card-sub">Activos, por última ocurrencia</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('admin.errores-servidor.index') }}" class="k2-btn k2-btn-ghost k2-btn-sm">Ver todos <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush">
                @if($erroresRecientes->isEmpty())
                    <div class="k2-empty"><i class="bi bi-shield-check"></i>Sin errores de servidor activos</div>
                @else
                    <div class="k2-table-wrap">
                        <table class="k2-table">
                            <thead>
                                <tr>
                                    <th>Error</th>
                                    <th>Endpoint</th>
                                    <th class="num">Veces</th>
                                    <th>Última</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($erroresRecientes as $err)
                                    @php [$eLabel, $eVar] = $errorEstado[$err->estado] ?? [$err->estado, 'neutral']; @endphp
                                    <tr>
                                        <td style="max-width:280px">
                                            <a href="{{ route('admin.errores-servidor.show', $err) }}" class="fw-semibold d-block text-truncate" style="color:var(--k2-ink)">
                                                {{ class_basename((string) $err->clase_error) ?: 'Error' }}
                                            </a>
                                            <div class="muted text-truncate">{{ \Illuminate\Support\Str::limit($err->mensaje_error, 90) }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="k2-badge neutral">{{ $err->metodo_http ?? '—' }}</span>
                                            <span class="muted">{{ \Illuminate\Support\Str::limit($err->endpoint, 34) }}</span>
                                        </td>
                                        <td class="num fw-semibold">{{ $err->cantidad_ocurrencias }}</td>
                                        <td class="text-nowrap muted">{{ ($err->ultima_ocurrencia_en ?? $err->created_at)?->diffForHumans() }}</td>
                                        <td><span class="k2-badge {{ $eVar }}">{{ $eLabel }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-journal-text"></i> Actividad reciente</h2>
                    <div class="k2-card-sub">Últimos registros de auditoría</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('logs.index') }}" class="k2-btn k2-btn-ghost k2-btn-sm">Ver logs <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush">
                @if($actividadReciente->isEmpty())
                    <div class="k2-empty"><i class="bi bi-journal"></i>Sin actividad registrada</div>
                @else
                    <ul class="k2-timeline">
                        @foreach($actividadReciente as $a)
                            @php
                                [$verbo, $var] = $eventoLog[$a->event ?? $a->description] ?? [$a->description, 'primary'];
                                $modelo = \App\Http\Controllers\LogController::labelModelo($a->subject_type);
                            @endphp
                            <li class="k2-tl-item">
                                <span class="k2-tl-dot {{ $var }}"></span>
                                <span class="k2-tl-text">
                                    <strong>{{ $a->causer?->nombre_completo ?? 'Sistema' }}</strong>
                                    {{ $verbo }}
                                    <span class="k2-badge neutral">{{ $modelo }}@if($a->subject_id) #{{ $a->subject_id }}@endif</span>
                                </span>
                                <span class="k2-tl-time">{{ $a->created_at?->diffForHumans() }} · {{ $a->created_at?->format('d/m/Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ INSTITUCIONES ═══════════════════════════════════════════════════════ --}}
<div class="k2-section-title">Estructura institucional</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-diagram-3"></i> Instituciones</h2>
                    <div class="k2-card-sub">Jerarquía con personal vigente y dispositivos operativos</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('instituciones.index') }}" class="k2-btn k2-btn-ghost k2-btn-sm">Administrar <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush">
                @if($instituciones->isEmpty())
                    <div class="k2-empty"><i class="bi bi-building"></i>No hay instituciones activas</div>
                @else
                    @php $maxPersonal = max(1, $instituciones->max('personal')); @endphp
                    <div class="k2-table-wrap">
                        <table class="k2-table">
                            <thead>
                                <tr>
                                    <th>Institución</th>
                                    <th style="width:36%">Personal vigente</th>
                                    <th class="num">Dispositivos</th>
                                    <th class="num">Sub-inst.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instituciones->take(10) as $fila)
                                    @php $inst = $fila['institucion']; @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2" style="padding-left: {{ $fila['nivel'] * 1.1 }}rem">
                                                @if($fila['nivel'] > 0)
                                                    <i class="bi bi-arrow-return-right" style="color:var(--k2-ink-3);font-size:.8rem"></i>
                                                @endif
                                                <div class="min-w-0">
                                                    <a href="{{ route('instituciones.show', $inst) }}" class="fw-semibold d-block text-truncate" style="max-width:260px;color:var(--k2-ink)">{{ $inst->nombre }}</a>
                                                    @if($inst->sigla)<div class="muted">{{ $inst->sigla }}</div>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="k2-bar flex-grow-1"><span style="width: {{ round($fila['personal'] / $maxPersonal * 100) }}%"></span></div>
                                                <span class="fw-semibold" style="min-width:2.5rem;text-align:right;font-variant-numeric:tabular-nums">{{ $fila['personal'] }}</span>
                                            </div>
                                        </td>
                                        <td class="num">{{ $fila['dispositivos'] }}</td>
                                        <td class="num">{{ $fila['hijas'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($institucionesTotal > 10)
                <div class="k2-card-foot">Mostrando 10 de {{ $institucionesTotal }} instituciones activas · <a href="{{ route('instituciones.index') }}" class="k2-link">Ver todas</a></div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-bar-chart-steps"></i> Personal por institución</h2>
                    <div class="k2-card-sub">Las {{ $personalTop->count() }} con mayor dotación vigente</div>
                </div>
            </div>
            <div class="k2-card-body">
                @if($personalTop->isEmpty())
                    <div class="k2-empty"><i class="bi bi-people"></i>Aún no hay designaciones vigentes</div>
                @else
                    <div class="k2-chart" style="height:{{ max(220, 44 * $personalTop->count() + 40) }}px">
                        <canvas id="k2ChartPersonal"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    if (!window.Chart) return;

    var serie       = @json($serie);
    var personalTop = @json($personalTop);
    var charts      = [];

    var PRIMARY = '#2E5EAA', DANGER = '#D64550';

    function tema() {
        return {
            grid: k2.isDark() ? 'rgba(255,255,255,.06)' : 'rgba(43,45,66,.07)',
            tick: k2.cssVar('--k2-ink-3') || '#8B93A7',
            tooltipBg: k2.isDark() ? '#1D2334' : '#2B2D42',
        };
    }

    function tooltipBase(t) {
        return {
            backgroundColor: t.tooltipBg,
            titleFont: { weight: '600' },
            padding: 10,
            cornerRadius: 8,
            displayColors: true,
            boxPadding: 4,
        };
    }

    /* ── Línea: marcas por día ───────────────────────────────────────── */
    var elMarcas = document.getElementById('k2ChartMarcas');
    if (elMarcas) {
        var t   = tema();
        var ctx = elMarcas.getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(46,94,170,.28)');
        grad.addColorStop(1, 'rgba(46,94,170,0)');

        charts.push(new Chart(elMarcas, {
            type: 'line',
            data: {
                labels: serie.labels,
                datasets: [
                    {
                        label: 'Marcas registradas',
                        data: serie.marcas,
                        borderColor: PRIMARY,
                        backgroundColor: grad,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: PRIMARY,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2,
                        tension: .35,
                        fill: true,
                    },
                    {
                        label: 'Jornadas con error',
                        data: serie.errores,
                        borderColor: DANGER,
                        backgroundColor: DANGER,
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: DANGER,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2,
                        tension: .35,
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipBase(t),
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: t.tick, maxTicksLimit: 10, maxRotation: 0 },
                        border: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 4,
                        grid: { color: t.grid },
                        ticks: { color: t.tick, precision: 0, padding: 6 },
                        border: { display: false },
                    },
                },
            },
        }));
    }

    /* ── Barras horizontales: personal por institución ───────────────── */
    var elPersonal = document.getElementById('k2ChartPersonal');
    if (elPersonal && personalTop.length) {
        var t2 = tema();
        charts.push(new Chart(elPersonal, {
            type: 'bar',
            data: {
                labels: personalTop.map(function (p) { return p.label; }),
                datasets: [{
                    label: 'Personal vigente',
                    data: personalTop.map(function (p) { return p.total; }),
                    backgroundColor: personalTop.map(function (_, i) { return i === 0 ? PRIMARY : 'rgba(46,94,170,.55)'; }),
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 26,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipBase(t2),
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: t2.grid },
                        ticks: { color: t2.tick, precision: 0 },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: t2.tick, font: { weight: '600' } },
                        border: { display: false },
                    },
                },
            },
        }));
    }

    /* ── Re-colorear al cambiar de tema ──────────────────────────────── */
    window.addEventListener('k2:theme', function () {
        var t = tema();
        charts.forEach(function (c) {
            Object.keys(c.options.scales).forEach(function (k) {
                var s = c.options.scales[k];
                if (s.grid && s.grid.display !== false) s.grid.color = t.grid;
                if (s.ticks) s.ticks.color = t.tick;
            });
            c.options.plugins.tooltip.backgroundColor = t.tooltipBg;
            c.update();
        });
    });
})();
</script>
@endpush
