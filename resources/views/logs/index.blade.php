@extends('layouts.app')

@section('title', 'Registro de actividad')

@section('breadcrumb')
    <li class="breadcrumb-item active">Registro de actividad</li>
@endsection

@push('styles')
<style>
    .event-badge-created  { background: #198754; color: #fff; }
    .event-badge-updated  { background: #0d6efd; color: #fff; }
    .event-badge-deleted  { background: #dc3545; color: #fff; }
    .event-badge-default  { background: #6c757d; color: #fff; }
</style>
@endpush

@section('content')

@php
    $eventoLabels = [
        'created' => ['Creó',     'event-badge-created'],
        'updated' => ['Modificó', 'event-badge-updated'],
        'deleted' => ['Eliminó',  'event-badge-deleted'],
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-journal-text me-1"></i> Registro de actividad
    </h5>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('logs.index') }}" class="row g-2 align-items-end">

            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Acción</label>
                <select name="evento" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    <option value="created"  @selected(request('evento') === 'created')>Creó</option>
                    <option value="updated"  @selected(request('evento') === 'updated')>Modificó</option>
                    <option value="deleted"  @selected(request('evento') === 'deleted')>Eliminó</option>
                </select>
            </div>

            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Entidad</label>
                <select name="modelo" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    @foreach($modelosPresentes as $m)
                        <option value="{{ $m }}" @selected(request('modelo') === $m)>
                            {{ $modelos[$m] ?? class_basename($m) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($esAdminGeneral || $esAdminInst)
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Usuario</label>
                <select name="causer_id" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    @foreach($usuariosFiltro as $u)
                        <option value="{{ $u->id }}" @selected(request('causer_id') == $u->id)>
                            {{ $u->apellidos }}, {{ $u->nombres }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('logs.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Limpiar</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="p-4 text-center text-muted small">
                <i class="bi bi-journal-x d-block mb-1 fs-4"></i>
                No hay registros de actividad.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:140px">Fecha y hora</th>
                            @if($esAdminGeneral || $esAdminInst)
                            <th>Usuario</th>
                            @endif
                            <th style="width:90px">Acción</th>
                            <th>Entidad</th>
                            <th>Detalle</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        @php
                            [$label, $cls] = $eventoLabels[$log->event] ?? [$log->event ?? '—', 'event-badge-default'];
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>

                            @if($esAdminGeneral || $esAdminInst)
                            <td>
                                @if($log->causer)
                                    <span class="fw-semibold">{{ $log->causer->apellidos }}</span>,
                                    {{ $log->causer->nombres }}
                                @else
                                    <span class="text-muted">Sistema</span>
                                @endif
                            </td>
                            @endif

                            <td>
                                <span class="badge {{ $cls }}">{{ $label }}</span>
                            </td>

                            <td>{{ $modelos[$log->subject_type] ?? class_basename((string) $log->subject_type) }}
                                @if($log->subject_id)
                                    <span class="text-muted">#{{ $log->subject_id }}</span>
                                @endif
                            </td>

                            <td class="text-muted">
                                @php
                                    $props = $log->properties->toArray();
                                    $attrs = $props['attributes'] ?? [];
                                    $old   = $props['old'] ?? [];
                                @endphp
                                @if($log->event === 'updated' && !empty($old))
                                    @php $camposModificados = array_keys($old); @endphp
                                    Campos: {{ implode(', ', array_slice($camposModificados, 0, 4)) }}{{ count($camposModificados) > 4 ? ' (+'.( count($camposModificados)-4).' más)' : '' }}
                                @elseif($log->event === 'created' && !empty($attrs))
                                    {{ count($attrs) }} campo(s) registrado(s)
                                @elseif($log->event === 'deleted' && !empty($old))
                                    ID eliminado: {{ $old['id'] ?? $log->subject_id ?? '—' }}
                                @else
                                    {{ $log->description }}
                                @endif
                            </td>

                            <td class="text-end">
                                <a href="{{ route('logs.show', $log) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center small text-muted">
                <span>Mostrando {{ $logs->firstItem() }}–{{ $logs->lastItem() }} de {{ $logs->total() }} registros</span>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
