@extends('layouts.app')

@section('title', $dispositivo->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dispositivos.index') }}">Dispositivos</a></li>
    <li class="breadcrumb-item active">{{ $dispositivo->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-hdd-network-fill me-1"></i> {{ $dispositivo->nombre }}
    </h5>
    <div>
        <a href="{{ route('dispositivos.edit', $dispositivo) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

@php
    $tipoMap  = ['biometrico' => ['Biométrico','secondary'], 'web' => ['Web','info'], 'otro' => ['Otro','light']];
    $modoMap  = ['directo_bd' => 'Directo a BD', 'importacion' => 'Importación', 'web' => 'Web'];
    [$tipoLabel, $tipoColor] = $tipoMap[$dispositivo->tipo] ?? [$dispositivo->tipo, 'light'];
@endphp

<div class="row g-3" style="max-width:820px">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Información general
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Institución</dt>
                    <dd class="col-7">
                        @if($dispositivo->institucion)
                            <a href="{{ route('instituciones.show', $dispositivo->institucion) }}" class="text-decoration-none">
                                {{ $dispositivo->institucion->nombre }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Ubicación</dt>
                    <dd class="col-7">{{ $dispositivo->ubicacion }}</dd>

                    <dt class="col-5 text-muted">Tipo</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $tipoColor }} text-dark">{{ $tipoLabel }}</span>
                    </dd>

                    <dt class="col-5 text-muted">Modo conexión</dt>
                    <dd class="col-7">{{ $modoMap[$dispositivo->modo_conexion] ?? $dispositivo->modo_conexion }}</dd>

                    <dt class="col-5 text-muted">Dirección IP</dt>
                    <dd class="col-7 font-monospace">{{ $dispositivo->ip_address ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $dispositivo->activo ? 'success' : 'secondary' }}">
                            {{ $dispositivo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-sliders me-1"></i> Configuración
            </div>
            <div class="card-body">
                @php $cfg = $dispositivo->configuracion ?? []; @endphp
                @if(!empty($cfg))
                    <dl class="row mb-0 small">
                        @if(array_key_exists('solicitar_contrasena', $cfg))
                            <dt class="col-7 text-muted">Solicitar contraseña al marcar</dt>
                            <dd class="col-5">
                                @if($dispositivo->requiereContrasena())
                                    <span class="badge bg-success">Sí</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </dd>
                        @endif
                        @foreach($cfg as $k => $v)
                            @if($k !== 'solicitar_contrasena')
                                <dt class="col-7 text-muted font-monospace">{{ $k }}</dt>
                                <dd class="col-5">{{ is_bool($v) ? ($v ? 'true' : 'false') : $v }}</dd>
                            @endif
                        @endforeach
                    </dl>
                @else
                    <p class="text-muted small mb-0 text-center py-3">
                        <i class="bi bi-sliders d-block mb-1 fs-5"></i>
                        Sin configuración adicional.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Terminales web registrados ──────────────────────────────────────── --}}
@if($dispositivo->esWeb() || $computadores->isNotEmpty())
<div class="mt-4" style="max-width:820px">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:var(--azul);color:#fff">
            <span><i class="bi bi-pc-display me-1"></i> Terminales web registrados</span>
            <span class="badge bg-light text-dark">{{ $computadores->count() }}</span>
        </div>

        @if($computadores->isEmpty())
            <div class="card-body text-center text-muted py-4 small">
                <i class="bi bi-pc-display d-block mb-2 fs-4"></i>
                Ningún equipo ha solicitado acceso todavía.<br>
                Cuando un navegador acceda a <code>/terminal</code> y complete el formulario, aparecerá aquí.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Equipo</th>
                            <th>Fingerprint</th>
                            <th>Estado</th>
                            <th>Registrado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($computadores as $comp)
                        <tr>
                            <td class="fw-semibold">
                                <i class="bi bi-pc-display me-1 text-muted"></i>
                                {{ $comp->nombre_equipo }}
                            </td>
                            <td class="font-monospace text-muted" style="font-size:.75rem">
                                {{ Str::limit($comp->fingerprint, 24) }}
                            </td>
                            <td>
                                @if($comp->autorizado)
                                    <span class="badge bg-success">Autorizado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $comp->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end">
                                {{-- Autorizar / Revocar --}}
                                <form method="POST"
                                      action="{{ route('dispositivos.computadores.autorizar', [$dispositivo, $comp]) }}"
                                      class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $comp->autorizado ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $comp->autorizado ? 'Revocar acceso' : 'Autorizar terminal' }}">
                                        <i class="bi {{ $comp->autorizado ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                        {{ $comp->autorizado ? 'Revocar' : 'Autorizar' }}
                                    </button>
                                </form>

                                {{-- Eliminar --}}
                                <form method="POST"
                                      action="{{ route('dispositivos.computadores.eliminar', [$dispositivo, $comp]) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar el terminal «{{ $comp->nombre_equipo }}»? Tendrá que volver a solicitar acceso.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endsection
