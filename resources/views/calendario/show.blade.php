@extends('layouts.app')

@section('title', $calendario->titulo)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('calendario.index') }}">Calendario</a></li>
    <li class="breadcrumb-item active">{{ $calendario->titulo }}</li>
@endsection

@section('content')

@php
    $colorMap = [
        'feriado'             => ['Feriado',             'danger',    ''],
        'suspension_total'    => ['Suspensión total',    'warning',   ''],
        'suspension_parcial'  => ['Suspensión parcial',  'info',      ''],
        'evento_condicional'  => ['Evento condicional',  'primary',   ''],
        'dia_no_laborable'    => ['Día no laborable',    'secondary', ''],
        'paro'                => ['Paro del personal',   '',          'background:#e67e22;color:#fff'],
    ];
    [$tipoLabel, $tipoColor, $tipoStyle] = $colorMap[$calendario->tipo] ?? [$calendario->tipo, 'secondary', ''];

    $efectoMap = [
        'retiro_anticipado' => 'Retiro anticipado',
        'ingreso_tardio'    => 'Ingreso tardío',
        'jornada_reducida'  => 'Jornada reducida',
        'exencion'          => 'Exención total',
    ];
    $condMap = [
        'sexo'            => 'Sexo',
        'cargo'           => 'Cargo',
        'categoria_cargo' => 'Categoría de cargo',
        'dependencia'     => 'Dependencia',
        'custom'          => 'Personalizado',
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-calendar-event me-1"></i> {{ $calendario->titulo }}
        <span class="badge ms-2 {{ $tipoColor ? 'bg-'.$tipoColor : '' }}" style="{{ $tipoStyle }}">{{ $tipoLabel }}</span>
    </h5>
    <div>
        <a href="{{ route('calendario.edit', $calendario) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <form method="POST" action="{{ route('calendario.destroy', $calendario) }}"
              class="d-inline"
              onsubmit="return confirm('¿Eliminar este evento?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger ms-1">
                <i class="bi bi-trash me-1"></i> Eliminar
            </button>
        </form>
        <a href="{{ route('calendario.index', ['mes' => $calendario->fecha_inicio->month, 'anio' => $calendario->fecha_inicio->year]) }}"
           class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3" style="max-width:900px">
    {{-- Datos del evento --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos del evento
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Alcance</dt>
                    <dd class="col-7">
                        @if($calendario->esGeneral())
                            <span class="badge bg-success">
                                <i class="bi bi-globe me-1"></i>General
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-building me-1"></i>Institucional
                            </span>
                        @endif
                    </dd>

                    @if(!$calendario->esGeneral())
                    <dt class="col-5 text-muted">Institución</dt>
                    <dd class="col-7">
                        @if($calendario->institucion)
                            <a href="{{ route('instituciones.show', $calendario->institucion) }}" class="text-decoration-none">
                                {{ $calendario->institucion->nombre }}
                            </a>
                        @else —
                        @endif
                    </dd>
                    @endif

                    <dt class="col-5 text-muted">
                        {{ $calendario->esMultiDia() ? 'Período' : 'Fecha' }}
                    </dt>
                    <dd class="col-7">
                        {{ $calendario->fecha_inicio->translatedFormat('d \d\e F \d\e Y') }}
                        @if($calendario->esMultiDia())
                            <span class="text-muted mx-1">→</span>
                            {{ $calendario->fecha_fin->translatedFormat('d \d\e F \d\e Y') }}
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Tipo</dt>
                    <dd class="col-7">
                        <span class="badge {{ $tipoColor ? 'bg-'.$tipoColor : '' }}" style="{{ $tipoStyle }}">{{ $tipoLabel }}</span>
                    </dd>

                    @if($calendario->hora_desde)
                        <dt class="col-5 text-muted">Hora desde</dt>
                        <dd class="col-7">{{ $calendario->hora_desde }}</dd>
                    @endif

                    @if($calendario->hora_hasta)
                        <dt class="col-5 text-muted">Hora hasta</dt>
                        <dd class="col-7">{{ $calendario->hora_hasta }}</dd>
                    @endif

                    <dt class="col-5 text-muted">Afecta cómputo</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $calendario->afecta_computo ? 'success' : 'secondary' }}">
                            {{ $calendario->afecta_computo ? 'Sí' : 'No' }}
                        </span>
                    </dd>
                </dl>

                @if($calendario->descripcion)
                    <hr class="my-2">
                    <p class="small mb-0 text-muted">{{ $calendario->descripcion }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Condiciones (evento_condicional) --}}
    @if($calendario->tipo === 'evento_condicional')
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-funnel me-1"></i> Condiciones
                <span class="badge bg-light text-dark ms-2">{{ $calendario->condiciones->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($calendario->condiciones->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Condición</th>
                                    <th>Valor</th>
                                    <th>Efecto</th>
                                    <th class="text-center">Minutos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calendario->condiciones as $cond)
                                    <tr>
                                        <td>{{ $condMap[$cond->tipo_condicion] ?? $cond->tipo_condicion }}</td>
                                        <td>{{ $cond->valor_condicion }}</td>
                                        <td>{{ $efectoMap[$cond->efecto] ?? $cond->efecto }}</td>
                                        <td class="text-center">
                                            {{ $cond->efecto === 'exencion' ? '—' : ($cond->minutos_afectados ?? '—') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 text-muted small text-center">
                        <i class="bi bi-funnel d-block mb-1 fs-5"></i>
                        Sin condiciones definidas.
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Filtros del paro --}}
    @if($calendario->tipo === 'paro')
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header text-white" style="background:#e67e22">
                <i class="bi bi-person-fill-slash me-1"></i> Filtros del paro
                @if($calendario->condiciones->isEmpty())
                    <span class="badge bg-light text-dark ms-2">Todos</span>
                @else
                    <span class="badge bg-light text-dark ms-2">{{ $calendario->condiciones->count() }} filtro(s)</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($calendario->condiciones->isEmpty())
                    <div class="p-3 small text-center text-muted">
                        <i class="bi bi-people-fill d-block mb-1 fs-5"></i>
                        Sin filtros — aplica a <strong>todo el personal</strong>.
                    </div>
                @else
                    <div class="p-2 small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Aplica solo a empleados que cumplan <em>al menos uno</em> de estos filtros:
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Filtrar por</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calendario->condiciones as $cond)
                                    <tr>
                                        <td>{{ $condMap[$cond->tipo_condicion] ?? $cond->tipo_condicion }}</td>
                                        <td>{{ $cond->valor_condicion }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
