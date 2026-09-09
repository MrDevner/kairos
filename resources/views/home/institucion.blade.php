@extends('layouts.app')

@php
    $esAdmin        = auth()->user()->permisos()->administrador()->tieneTodosLosPermisos();
    $puedeAutorizar = $instActiva->puedeAutorizarLicencias(auth()->user());
    $iniciales      = fn ($u) => strtoupper(substr($u?->nombres ?? '', 0, 1) . substr($u?->apellidos ?? '', 0, 1)) ?: '—';
    $tipoEvento     = [
        'feriado'            => ['Feriado',       'danger'],
        'dia_no_laborable'   => ['No laborable',  'neutral'],
        'suspension_total'   => ['Susp. total',   'amber'],
        'suspension_parcial' => ['Susp. parcial', 'primary'],
        'evento_condicional' => ['Condicional',   'primary'],
    ];
@endphp

@section('title', $instActiva->sigla ?: $instActiva->nombre)

@section('page-title')
    {{ $instActiva->nombre }}
    @if($instActiva->sigla)
        <span class="k2-badge neutral align-middle ms-1">{{ $instActiva->sigla }}</span>
    @endif
    @if($instActiva->activa)
        <span class="k2-badge teal align-middle"><span class="k2-pulse"></span> Activa</span>
    @else
        <span class="k2-badge neutral align-middle">Inactiva</span>
    @endif
@endsection

@section('page-subtitle')
    @if(count($instRuta) > 1)
        {{ implode(' / ', $instRuta) }} ·
    @endif
    {{ $hoy->isoFormat('dddd D [de] MMMM [de] YYYY') }}
@endsection

@section('breadcrumb')
    <li class="active">Dashboard institucional</li>
@endsection

@section('page-actions')
    @if($esAdmin)
        <a href="{{ route('home') }}" class="k2-btn"><i class="bi bi-grid-1x2"></i> Panel general</a>
        <a href="{{ route('instituciones.show', $instActiva) }}" class="k2-btn k2-btn-primary"><i class="bi bi-gear"></i> Configurar</a>
    @endif
@endsection

@section('content')

{{-- ══ INDICADORES ═════════════════════════════════════════════════════════ --}}
<div class="row g-3">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('designaciones.index') }}" class="k2-card k2-kpi">
            <span class="k2-kpi-icon"><i class="bi bi-people"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Personal vigente</span>
                <span class="k2-kpi-value d-block">{{ $instStats['personal_vigente'] }}</span>
                <span class="k2-kpi-meta">
                    @if($instStats['personal_total'] > $instStats['personal_vigente'])
                        <span>{{ $instStats['personal_total'] }} designaciones en total</span>
                    @else
                        <span>designaciones activas hoy</span>
                    @endif
                </span>
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('licencias.index', ['estado' => 'pendiente']) }}" class="k2-card k2-kpi amber">
            <span class="k2-kpi-icon"><i class="bi bi-calendar-check"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Licencias pendientes</span>
                <span class="k2-kpi-value d-block">{{ $instStats['licencias_pendientes'] }}</span>
                <span class="k2-kpi-meta">
                    @if($instStats['licencias_pendientes'] > 0)
                        <span class="k2-delta down"><i class="bi bi-hourglass-split"></i> requieren aprobación</span>
                    @else
                        <span class="k2-delta up"><i class="bi bi-check2"></i> al día</span>
                    @endif
                </span>
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('calendario.index') }}" class="k2-card k2-kpi teal">
            <span class="k2-kpi-icon"><i class="bi bi-calendar-event"></i></span>
            <span class="k2-kpi-body">
                <span class="k2-kpi-label">Eventos esta semana</span>
                <span class="k2-kpi-value d-block">{{ $instStats['eventos_proximos'] }}</span>
                <span class="k2-kpi-meta"><span>en los próximos 7 días</span></span>
            </span>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        @if($instStats['sub_instituciones'] > 0)
            <a href="{{ route('instituciones.index') }}" class="k2-card k2-kpi">
                <span class="k2-kpi-icon"><i class="bi bi-diagram-3"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Sub-instituciones</span>
                    <span class="k2-kpi-value d-block">{{ $instStats['sub_instituciones'] }}</span>
                    <span class="k2-kpi-meta"><span>dependen de esta institución</span></span>
                </span>
            </a>
        @else
            <a href="{{ route('tipos-licencia.index') }}" class="k2-card k2-kpi">
                <span class="k2-kpi-icon"><i class="bi bi-card-list"></i></span>
                <span class="k2-kpi-body">
                    <span class="k2-kpi-label">Tipos de licencia</span>
                    <span class="k2-kpi-value d-block">{{ $instStats['tipos_licencia'] }}</span>
                    <span class="k2-kpi-meta"><span>disponibles para el personal</span></span>
                </span>
            </a>
        @endif
    </div>
</div>

{{-- ══ PANELES ═════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mt-0">

    {{-- Licencias pendientes --}}
    <div class="col-lg-7">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title">
                        <i class="bi bi-hourglass-split"></i> Licencias pendientes de aprobación
                        @if($instStats['licencias_pendientes'] > 0)
                            <span class="k2-badge amber">{{ $instStats['licencias_pendientes'] }}</span>
                        @endif
                    </h2>
                    <div class="k2-card-sub">Próximas a iniciar primero</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('licencias.index', ['estado' => 'pendiente']) }}" class="k2-btn k2-btn-ghost k2-btn-sm">Ver todas <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush">
                @forelse($licenciasPendientes as $lic)
                    <div class="k2-list-item">
                        <span class="k2-initials">{{ $iniciales($lic->usuario) }}</span>
                        <span class="k2-list-main">
                            <span class="k2-list-title">{{ $lic->usuario->nombre_completo ?? '—' }}</span>
                            <span class="k2-list-meta">
                                {{ $lic->tipoLicencia->nombre ?? '—' }}
                                · {{ $lic->fecha_inicio?->format('d/m/Y') }}
                                → {{ $lic->fecha_fin?->format('d/m/Y') ?? '∞' }}
                            </span>
                        </span>
                        <span class="d-flex gap-1 flex-shrink-0">
                            @if($puedeAutorizar)
                                <form method="POST" action="{{ route('licencias.aprobar', $lic) }}">
                                    @csrf
                                    <button class="k2-btn k2-btn-sm k2-btn-teal" title="Aprobar"
                                            onclick="return confirm('¿Aprobar la licencia de {{ addslashes($lic->usuario->nombre_completo ?? '') }}?')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('licencias.show', $lic) }}" class="k2-btn k2-btn-sm" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </span>
                    </div>
                @empty
                    <div class="k2-empty"><i class="bi bi-check2-circle"></i>Sin licencias pendientes</div>
                @endforelse
            </div>

            @if($licenciasRecientes->isNotEmpty())
                <div class="k2-card-foot">
                    <div class="fw-semibold mb-1"><i class="bi bi-clock-history me-1"></i> Resueltas esta semana</div>
                    @foreach($licenciasRecientes as $lic)
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span>{{ $lic->usuario->nombre_completo ?? '—' }} <span class="opacity-75">· {{ $lic->tipoLicencia->nombre ?? '' }}</span></span>
                            @if($lic->estaAprobada())
                                <span class="k2-badge teal">Aprobada</span>
                            @else
                                <span class="k2-badge danger">Rechazada</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Calendario próximo --}}
    <div class="col-lg-5">
        <div class="k2-card">
            <div class="k2-card-head">
                <div>
                    <h2 class="k2-card-title"><i class="bi bi-calendar3"></i> Próximos 30 días</h2>
                    <div class="k2-card-sub">Feriados, suspensiones y eventos de la institución</div>
                </div>
                <div class="k2-card-tools">
                    <a href="{{ route('calendario.index') }}" class="k2-btn k2-btn-ghost k2-btn-sm">Calendario <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="k2-card-body flush" style="max-height:460px;overflow-y:auto">
                @forelse($eventosProximos as $ev)
                    @php
                        [$tipoLabel, $tipoVar] = $tipoEvento[$ev->tipo] ?? [$ev->tipo, 'neutral'];
                        $esHoy    = $ev->fecha_inicio->isToday()
                                    || ($ev->fecha_fin && now()->between($ev->fecha_inicio, $ev->fecha_fin));
                        $esManana = !$esHoy && $ev->fecha_inicio->isTomorrow();
                    @endphp
                    <div class="k2-list-item {{ $esHoy ? 'hoy' : '' }}">
                        <span class="k2-date">
                            <b>{{ $ev->fecha_inicio->format('d') }}</b>
                            <small>{{ $ev->fecha_inicio->isoFormat('MMM') }}</small>
                        </span>
                        <span class="k2-list-main">
                            <span class="k2-list-title">
                                {{ $ev->titulo }}
                                @if($esHoy)
                                    <span class="k2-badge amber ms-1">Hoy</span>
                                @elseif($esManana)
                                    <span class="k2-badge primary ms-1">Mañana</span>
                                @endif
                            </span>
                            <span class="k2-list-meta">
                                <span class="k2-badge {{ $tipoVar }}">{{ $tipoLabel }}</span>
                                @if($ev->fecha_fin && !$ev->fecha_inicio->isSameDay($ev->fecha_fin))
                                    hasta {{ $ev->fecha_fin->isoFormat('D MMM') }}
                                @elseif($ev->hora_desde)
                                    {{ substr($ev->hora_desde, 0, 5) }}@if($ev->hora_hasta) – {{ substr($ev->hora_hasta, 0, 5) }}@endif
                                @endif
                            </span>
                        </span>
                    </div>
                @empty
                    <div class="k2-empty"><i class="bi bi-calendar-x"></i>Sin eventos en los próximos 30 días</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
