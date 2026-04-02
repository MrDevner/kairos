@extends('layouts.app')

@section('title', 'Calendario')

@section('breadcrumb')
    <li class="breadcrumb-item active">Calendario</li>
@endsection

@push('styles')
<style>
    .cal-table { table-layout: fixed; width: 100%; }
    .cal-table th { background: var(--azul); color: #fff; text-align: center; padding: .4rem; font-size: .82rem; }
    .cal-table td {
        vertical-align: top;
        padding: .35rem;
        height: 90px;
        font-size: .78rem;
        border: 1px solid #dee2e6;
        background: #fff;
    }
    .cal-table td.otro-mes { background: #f8f9fa; color: #adb5bd; }
    .cal-table td.hoy { background: #fff9e6; }
    .cal-day-num { font-weight: 700; font-size: .85rem; margin-bottom: .2rem; }
    .cal-event { display: block; border-radius: .25rem; padding: 1px 4px; margin-bottom: 2px;
                 font-size: .7rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-calendar3 me-1"></i> Calendario
    </h5>
    <a href="{{ route('calendario.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo evento
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('calendario.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Mes</label>
                <select name="mes" class="form-select form-select-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" @selected($mes == $m)>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Año</label>
                <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" min="2000" max="2099">
            </div>
            <div class="col-sm-4">
                <label class="form-label form-label-sm mb-0 small">Institución</label>
                <select name="institucion" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(request('institucion') == $inst->id)>{{ $inst->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Ver
                </button>
                <a href="{{ route('calendario.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Hoy</a>
            </div>
        </form>
    </div>
</div>

{{-- Leyenda --}}
<div class="d-flex flex-wrap gap-2 mb-3 small">
    <span class="badge bg-danger">Feriado</span>
    <span class="badge bg-warning text-dark">Suspensión total</span>
    <span class="badge bg-info text-dark">Suspensión parcial</span>
    <span class="badge bg-primary">Evento condicional</span>
    <span class="badge bg-secondary">Día no laborable</span>
</div>

{{-- Cabecera del mes --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <a href="{{ route('calendario.index', ['mes' => $mes == 1 ? 12 : $mes - 1, 'anio' => $mes == 1 ? $anio - 1 : $anio, 'institucion' => request('institucion')]) }}"
           class="text-white me-2 text-decoration-none"><i class="bi bi-chevron-left"></i></a>
        <span class="flex-grow-1 text-center fw-semibold">
            {{ \Carbon\Carbon::create($anio, $mes)->translatedFormat('F Y') }}
        </span>
        <a href="{{ route('calendario.index', ['mes' => $mes == 12 ? 1 : $mes + 1, 'anio' => $mes == 12 ? $anio + 1 : $anio, 'institucion' => request('institucion')]) }}"
           class="text-white ms-2 text-decoration-none"><i class="bi bi-chevron-right"></i></a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="cal-table">
                <thead>
                    <tr>
                        <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $primerDia = \Carbon\Carbon::create($anio, $mes, 1);
                        // Día de la semana: 1=Lun ... 7=Dom
                        $inicio = $primerDia->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                        $fin    = $primerDia->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::MONDAY);
                        $cursor = $inicio->copy();
                        $hoy    = \Carbon\Carbon::today();

                        // Mapear eventos por fecha
                        $eventosPorFecha = [];
                        foreach($eventos as $ev) {
                            $evFecha = \Carbon\Carbon::parse($ev->fecha)->format('Y-m-d');
                            $eventosPorFecha[$evFecha][] = $ev;
                        }

                        $colorMap = [
                            'feriado'             => 'danger',
                            'suspension_total'    => 'warning',
                            'suspension_parcial'  => 'info',
                            'evento_condicional'  => 'primary',
                            'dia_no_laborable'    => 'secondary',
                        ];
                    @endphp
                    @while($cursor <= $fin)
                        <tr>
                            @for($col = 0; $col < 7; $col++)
                                @php
                                    $esOtroMes = $cursor->month != $mes;
                                    $esHoy     = $cursor->isSameDay($hoy);
                                    $clases    = $esOtroMes ? 'otro-mes' : ($esHoy ? 'hoy' : '');
                                    $fKey      = $cursor->format('Y-m-d');
                                    $evsDia    = $eventosPorFecha[$fKey] ?? [];
                                @endphp
                                <td class="{{ $clases }}">
                                    <div class="cal-day-num">{{ $cursor->day }}</div>
                                    @foreach($evsDia as $ev)
                                        @php $color = $colorMap[$ev->tipo] ?? 'secondary'; @endphp
                                        <a href="{{ route('calendario.show', $ev) }}"
                                           class="cal-event badge bg-{{ $color }} text-decoration-none"
                                           title="{{ $ev->descripcion ?? $ev->tipo }}">
                                            {{ Str::limit($ev->descripcion ?? ucfirst(str_replace('_',' ',$ev->tipo)), 22) }}
                                        </a>
                                    @endforeach
                                    @if(!$esOtroMes)
                                        <a href="{{ route('calendario.create', ['fecha' => $fKey]) }}"
                                           class="text-muted text-decoration-none" style="font-size:.65rem" title="Agregar evento">+</a>
                                    @endif
                                </td>
                                @php $cursor->addDay(); @endphp
                            @endfor
                        </tr>
                    @endwhile
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
