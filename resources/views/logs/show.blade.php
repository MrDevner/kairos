@extends('layouts.app')

@section('title', 'Detalle de actividad')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('logs.index') }}">Registro de actividad</a></li>
    <li class="breadcrumb-item active">Detalle #{{ $activity->id }}</li>
@endsection

@push('styles')
<style>
    .diff-table td:first-child { width: 30%; font-weight: 600; }
    .val-old { color: #dc3545; text-decoration: line-through; }
    .val-new { color: #198754; }
    .val-same { color: #6c757d; }
    .prop-key { font-family: monospace; font-size: .8rem; color: #495057; }
</style>
@endpush

@section('content')

@php
    $eventoMap = [
        'created' => ['Creó',     'success'],
        'updated' => ['Modificó', 'primary'],
        'deleted' => ['Eliminó',  'danger'],
    ];
    [$eventoLabel, $eventoColor] = $eventoMap[$activity->event] ?? [$activity->event ?? '—', 'secondary'];

    $props = $activity->properties->toArray();
    $attrs = $props['attributes'] ?? [];
    $old   = $props['old'] ?? [];

    // Para updated: solo mostrar los campos que cambiaron
    // Para created/deleted: mostrar todos los campos
    if ($activity->event === 'updated') {
        $campos = array_keys($old);
    } elseif ($activity->event === 'deleted') {
        $campos = array_keys($old ?: $attrs);
    } else {
        $campos = array_keys($attrs);
    }

    // Campos a omitir en la vista (internos, sin interés)
    $omitir = ['updated_at', 'created_at', 'deleted_at', 'remember_token', 'token_recuerdo', 'password'];
    $campos = array_filter($campos, fn($c) => !in_array($c, $omitir));
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-journal-text me-1"></i> Detalle de actividad
        <span class="badge bg-{{ $eventoColor }} ms-2">{{ $eventoLabel }}</span>
    </h5>
    <a href="{{ route('logs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row g-3" style="max-width:900px">

    {{-- Encabezado --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Información general
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3 text-muted">Fecha y hora</dt>
                    <dd class="col-sm-9">{{ $activity->created_at->format('d/m/Y H:i:s') }}</dd>

                    <dt class="col-sm-3 text-muted">Usuario</dt>
                    <dd class="col-sm-9">
                        @if($activity->causer)
                            <strong>{{ $activity->causer->apellidos }}, {{ $activity->causer->nombres }}</strong>
                            <span class="text-muted ms-1">(ID {{ $activity->causer->id }})</span>
                        @else
                            <span class="text-muted">Sistema / Seeder</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3 text-muted">Acción</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $eventoColor }}">{{ $eventoLabel }}</span>
                    </dd>

                    <dt class="col-sm-3 text-muted">Entidad</dt>
                    <dd class="col-sm-9">
                        {{ $modelos[$activity->subject_type] ?? class_basename((string) $activity->subject_type) }}
                        @if($activity->subject_id)
                            <span class="text-muted ms-1">#{{ $activity->subject_id }}</span>
                        @endif
                    </dd>

                    @if($activity->log_name && $activity->log_name !== 'default')
                    <dt class="col-sm-3 text-muted">Canal</dt>
                    <dd class="col-sm-9">{{ $activity->log_name }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- Datos modificados --}}
    @if(!empty($campos))
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                @if($activity->event === 'updated')
                    <i class="bi bi-pencil-square me-1"></i> Campos modificados
                @elseif($activity->event === 'created')
                    <i class="bi bi-plus-circle me-1"></i> Datos registrados
                @else
                    <i class="bi bi-trash me-1"></i> Datos eliminados
                @endif
                <span class="badge bg-light text-dark ms-2">{{ count($campos) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm diff-table mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Campo</th>
                                @if($activity->event === 'updated')
                                    <th>Valor anterior</th>
                                    <th>Valor nuevo</th>
                                @else
                                    <th>Valor</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campos as $campo)
                            @php
                                $valNuevo = $attrs[$campo] ?? null;
                                $valAntiguo = $old[$campo] ?? null;
                                $sinCambio = $activity->event === 'updated' && $valAntiguo === $valNuevo;
                            @endphp
                            <tr>
                                <td class="prop-key">{{ $campo }}</td>
                                @if($activity->event === 'updated')
                                    <td>
                                        @if($valAntiguo === null)
                                            <em class="text-muted">null</em>
                                        @elseif(is_bool($valAntiguo))
                                            <span class="val-old">{{ $valAntiguo ? 'Sí' : 'No' }}</span>
                                        @else
                                            <span class="val-old">{{ Str::limit((string) $valAntiguo, 120) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($valNuevo === null)
                                            <em class="text-muted">null</em>
                                        @elseif(is_bool($valNuevo))
                                            <span class="val-new">{{ $valNuevo ? 'Sí' : 'No' }}</span>
                                        @else
                                            <span class="val-new">{{ Str::limit((string) $valNuevo, 120) }}</span>
                                        @endif
                                    </td>
                                @else
                                    @php $val = $attrs[$campo] ?? ($old[$campo] ?? null); @endphp
                                    <td>
                                        @if($val === null)
                                            <em class="text-muted">null</em>
                                        @elseif(is_bool($val))
                                            {{ $val ? 'Sí' : 'No' }}
                                        @else
                                            {{ Str::limit((string) $val, 200) }}
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-info small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            No hay campos registrados en este evento.
        </div>
    </div>
    @endif

</div>
@endsection
