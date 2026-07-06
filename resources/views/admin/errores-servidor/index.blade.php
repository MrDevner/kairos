@extends('layouts.app')

@section('title', 'Errores de servidor')

@section('breadcrumb')
    <li class="breadcrumb-item active">Errores de servidor</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-bug-fill me-1"></i> Errores de servidor
    </h5>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold" style="color:var(--azul)">{{ $kpis['total_abiertos'] }}</div>
                <div class="small text-muted">Abiertos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold" style="color:var(--azul)">{{ $kpis['total_activos'] }}</div>
                <div class="small text-muted">Activos (abiertos + en revisión)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold" style="color:var(--azul)">{{ $kpis['ocurrencias_24h'] }}</div>
                <div class="small text-muted">Ocurrencias (24h)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <div class="fs-6 fw-bold" style="color:var(--azul)">
                    {{ $kpis['mttr_minutos'] !== null ? $kpis['mttr_minutos'].' min' : '—' }}
                </div>
                <div class="small text-muted">MTTR</div>
            </div>
        </div>
    </div>
    @if($kpis['endpoint_top'])
    <div class="col-12">
        <div class="alert alert-warning small mb-0">
            <i class="bi bi-graph-up-arrow me-1"></i>
            Endpoint con más ocurrencias en 24h: <strong>{{ $kpis['endpoint_top'] }}</strong>
            ({{ $kpis['endpoint_top_total'] }} ocurrencias)
        </div>
    </div>
    @endif
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'activos' ? 'active' : '' }}"
           href="{{ route('admin.errores-servidor.index', array_merge(request()->except('tab','page'), ['tab' => 'activos'])) }}">
            Activos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'cerrados' ? 'active' : '' }}"
           href="{{ route('admin.errores-servidor.index', array_merge(request()->except('tab','page'), ['tab' => 'cerrados'])) }}">
            Cerrados
        </a>
    </li>
</ul>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.errores-servidor.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Estado</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    @foreach(['abierto','en_revision','mitigado','solucionado'] as $e)
                        <option value="{{ $e }}" @selected(request('status') === $e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Método</label>
                <select name="method" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                        <option value="{{ $m }}" @selected(request('method') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Mensaje, endpoint, clase, correlation id…">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('admin.errores-servidor.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-body p-0">
        @if($errores->isEmpty())
            <div class="p-4 text-center text-muted small">
                <i class="bi bi-check-circle d-block mb-1 fs-4"></i>
                No hay errores {{ $tab === 'activos' ? 'activos' : 'cerrados' }}.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Estado</th>
                            <th>Mensaje</th>
                            <th>Endpoint</th>
                            <th class="text-center">Ocurrencias</th>
                            <th>Última ocurrencia</th>
                            <th>Asignado</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errores as $error)
                        <tr>
                            <td><span class="badge bg-{{ ['abierto'=>'danger','en_revision'=>'warning','mitigado'=>'info','solucionado'=>'success'][$error->estado] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_',' ',$error->estado)) }}
                            </span></td>
                            <td class="text-truncate" style="max-width:280px" title="{{ $error->mensaje_error }}">
                                <span class="fw-semibold">{{ Str::limit($error->clase_error, 40) }}</span><br>
                                <span class="text-muted">{{ Str::limit($error->mensaje_error, 60) }}</span>
                            </td>
                            <td class="text-truncate" style="max-width:200px">
                                <span class="badge bg-secondary">{{ $error->metodo_http }}</span>
                                {{ Str::limit($error->endpoint, 40) }}
                            </td>
                            <td class="text-center fw-semibold">{{ $error->cantidad_ocurrencias }}</td>
                            <td class="text-muted">{{ $error->ultima_ocurrencia_en?->format('d/m/Y H:i') }}</td>
                            <td>{{ $error->asignadoA?->nombre_completo ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.errores-servidor.show', $error) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center small text-muted">
                <span>Mostrando {{ $errores->firstItem() }}–{{ $errores->lastItem() }} de {{ $errores->total() }} registros</span>
                {{ $errores->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
