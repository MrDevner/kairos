@php
    $user   = auth()->user();
    $instId = $instActiva->id;
    $esAdmin = $user->permisos()->administrador()->tieneTodosLosPermisos();
    $tipoMap = [
        'feriado'             => ['Feriado',           'danger'],
        'dia_no_laborable'    => ['No laborable',       'secondary'],
        'suspension_total'    => ['Susp. total',        'warning'],
        'suspension_parcial'  => ['Susp. parcial',      'info'],
        'evento_condicional'  => ['Condicional',        'primary'],
    ];
@endphp

{{-- ── Encabezado institucional ──────────────────────────────────────── --}}
<div class="card mb-4" style="border-left: 5px solid var(--azul)">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1" style="color:var(--azul)">
                    <i class="bi bi-building-fill me-2"></i>
                    {{ $instActiva->nombre }}
                    @if($instActiva->sigla)
                        <span class="text-muted fw-normal fs-6 ms-1">({{ $instActiva->sigla }})</span>
                    @endif
                </h5>

                {{-- Ruta jerárquica --}}
                @if(count($instRuta) > 1)
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-chevron mb-0" style="font-size:.8rem">
                            @foreach($instRuta as $i => $nombre)
                                @if($i < count($instRuta) - 1)
                                    <li class="breadcrumb-item text-muted">{{ $nombre }}</li>
                                @else
                                    <li class="breadcrumb-item active fw-semibold">{{ $nombre }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($instActiva->activa)
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Activa</span>
                @else
                    <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>Inactiva</span>
                @endif
                @if($esAdmin)
                    <a href="{{ route('instituciones.show', $instActiva) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-gear me-1"></i> Configurar
                    </a>
                    <a href="{{ route('home', ['vista' => 'admin']) }}"
                       class="btn btn-sm btn-outline-secondary"
                       title="Ver dashboard de Administrador General">
                        <i class="bi bi-grid me-1"></i> Vista admin
                    </a>
                @endif
                <span class="text-muted small">{{ now()->isoFormat('dddd D [de] MMMM') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Tarjetas de estadísticas ──────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Personal vigente --}}
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center" style="border-top:3px solid var(--azul)">
            <div class="card-body py-3">
                <i class="bi bi-people-fill fs-2" style="color:var(--azul)"></i>
                <div class="fs-3 fw-bold mt-1">{{ $instStats['personal_vigente'] }}</div>
                <div class="text-muted small">Personal vigente</div>
                @if($instStats['personal_total'] > $instStats['personal_vigente'])
                    <div class="text-muted" style="font-size:.7rem">
                        {{ $instStats['personal_total'] }} totales
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Licencias pendientes --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('licencias.index', ['estado' => 'pendiente']) }}" class="text-decoration-none">
            <div class="card h-100 text-center" style="border-top:3px solid #ffc107">
                <div class="card-body py-3">
                    <i class="bi bi-calendar-check fs-2 text-warning"></i>
                    <div class="fs-3 fw-bold mt-1 {{ $instStats['licencias_pendientes'] > 0 ? 'text-warning' : '' }}">
                        {{ $instStats['licencias_pendientes'] }}
                    </div>
                    <div class="text-muted small">Licencias pendientes</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Eventos próximos (7 días) --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('calendario.index') }}" class="text-decoration-none">
            <div class="card h-100 text-center" style="border-top:3px solid var(--celeste)">
                <div class="card-body py-3">
                    <i class="bi bi-calendar-event-fill fs-2" style="color:var(--celeste)"></i>
                    <div class="fs-3 fw-bold mt-1">{{ $instStats['eventos_proximos'] }}</div>
                    <div class="text-muted small">Eventos esta semana</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Sub-instituciones / Tipos licencia disponibles --}}
    <div class="col-6 col-md-3">
        @if($instStats['sub_instituciones'] > 0)
            <div class="card h-100 text-center" style="border-top:3px solid #6c757d">
                <div class="card-body py-3">
                    <i class="bi bi-diagram-3-fill fs-2 text-secondary"></i>
                    <div class="fs-3 fw-bold mt-1">{{ $instStats['sub_instituciones'] }}</div>
                    <div class="text-muted small">Sub-instituciones</div>
                </div>
            </div>
        @else
            <div class="card h-100 text-center" style="border-top:3px solid #0dcaf0">
                <div class="card-body py-3">
                    <i class="bi bi-card-list fs-2 text-info"></i>
                    <div class="fs-3 fw-bold mt-1">
                        {{ \App\Models\TipoLicencia::activos()->visiblesParaInstitucion($instId)->count() }}
                    </div>
                    <div class="text-muted small">Tipos de licencia</div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ── Paneles principales ───────────────────────────────────────────── --}}
<div class="row g-3">

    {{-- Licencias pendientes ──────────────────────────────────────────── --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center"
                 style="background:var(--azul);color:#fff">
                <i class="bi bi-hourglass-split me-2"></i> Licencias pendientes de aprobación
                @if($instStats['licencias_pendientes'] > 0)
                    <span class="badge bg-warning text-dark ms-2">
                        {{ $instStats['licencias_pendientes'] }}
                    </span>
                @endif
                <a href="{{ route('licencias.index', ['estado' => 'pendiente']) }}"
                   class="btn btn-sm btn-light py-0 ms-auto" style="font-size:.75rem">
                    Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($licenciasPendientes as $lic)
                    <div class="px-3 py-2 border-bottom d-flex align-items-center gap-3
                                {{ $loop->last ? '' : '' }}">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">
                                {{ $lic->usuario->nombre_completo ?? '—' }}
                            </div>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $lic->tipoLicencia->nombre ?? '—' }}
                                &nbsp;·&nbsp;
                                {{ $lic->fecha_inicio?->format('d/m/Y') }}
                                @if($lic->fecha_fin)
                                    → {{ $lic->fecha_fin->format('d/m/Y') }}
                                @else
                                    → ∞
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            @if($instActiva->puedeAutorizarLicencias(auth()->user()))
                                <form method="POST" action="{{ route('licencias.aprobar', $lic) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success py-0 px-2"
                                            title="Aprobar"
                                            onclick="return confirm('¿Aprobar la licencia de {{ addslashes($lic->usuario->nombre_completo ?? '') }}?')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('licencias.show', $lic) }}"
                               class="btn btn-sm btn-outline-primary py-0 px-2" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-check-circle-fill text-success d-block fs-3 mb-1"></i>
                        Sin licencias pendientes
                    </div>
                @endforelse
            </div>

            {{-- Resueltas recientemente --}}
            @if($licenciasRecientes->isNotEmpty())
                <div class="card-footer bg-light py-0" style="font-size:.75rem">
                    <div class="fw-semibold text-muted py-2 px-1">
                        <i class="bi bi-clock-history me-1"></i> Resueltas esta semana
                    </div>
                    @foreach($licenciasRecientes as $lic)
                        <div class="d-flex justify-content-between align-items-center
                                    border-top py-1 px-1">
                            <span>{{ $lic->usuario->nombre_completo ?? '—' }}</span>
                            <span>
                                @if($lic->estaAprobada())
                                    <span class="badge bg-success">Aprobada</span>
                                @else
                                    <span class="badge bg-danger">Rechazada</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Calendario próximo ──────────────────────────────────────────────── --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center"
                 style="background:var(--azul);color:#fff">
                <i class="bi bi-calendar3 me-2"></i> Próximos 30 días
                <a href="{{ route('calendario.index') }}"
                   class="btn btn-sm btn-light py-0 ms-auto" style="font-size:.75rem">
                    Ver calendario
                </a>
            </div>
            <div class="card-body p-0" style="max-height:420px;overflow-y:auto">
                @forelse($eventosProximos as $ev)
                    @php
                        [$tipoLabel, $tipoColor] = $tipoMap[$ev->tipo] ?? [$ev->tipo, 'secondary'];
                        $esHoy    = $ev->fecha_inicio->isToday()
                                    || ($ev->fecha_fin && now()->between($ev->fecha_inicio, $ev->fecha_fin));
                        $esMañana = !$esHoy && $ev->fecha_inicio->isTomorrow();
                    @endphp
                    <div class="px-3 py-2 border-bottom d-flex align-items-start gap-2
                                {{ $esHoy ? 'bg-warning bg-opacity-10' : '' }}">
                        {{-- Fecha --}}
                        <div class="text-center flex-shrink-0"
                             style="min-width:42px">
                            <div class="fw-bold" style="font-size:.9rem;color:var(--azul)">
                                {{ $ev->fecha_inicio->format('d') }}
                            </div>
                            <div class="text-muted text-uppercase"
                                 style="font-size:.65rem;line-height:1">
                                {{ $ev->fecha_inicio->isoFormat('MMM') }}
                            </div>
                        </div>
                        {{-- Detalle --}}
                        <div class="flex-grow-1">
                            <div class="small fw-semibold">
                                {{ $ev->titulo }}
                                @if($esHoy)
                                    <span class="badge bg-warning text-dark ms-1"
                                          style="font-size:.65rem">Hoy</span>
                                @elseif($esMañana)
                                    <span class="badge bg-info text-dark ms-1"
                                          style="font-size:.65rem">Mañana</span>
                                @endif
                            </div>
                            <div>
                                <span class="badge bg-{{ $tipoColor }}"
                                      style="font-size:.65rem">{{ $tipoLabel }}</span>
                                @if($ev->fecha_fin && !$ev->fecha_inicio->isSameDay($ev->fecha_fin))
                                    <span class="text-muted ms-1" style="font-size:.7rem">
                                        hasta {{ $ev->fecha_fin->isoFormat('D MMM') }}
                                    </span>
                                @elseif($ev->hora_desde)
                                    <span class="text-muted ms-1" style="font-size:.7rem">
                                        {{ substr($ev->hora_desde,0,5) }}
                                        @if($ev->hora_hasta) – {{ substr($ev->hora_hasta,0,5) }} @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-calendar-x d-block fs-3 mb-1"></i>
                        Sin eventos en los próximos 30 días
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
