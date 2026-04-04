@extends('layouts.app')

@section('title', $tipo->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tipos-licencia.index') }}">Tipos de licencia</a></li>
    <li class="breadcrumb-item active">{{ $tipo->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-card-list me-1"></i> {{ $tipo->nombre }}
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('tipos-licencia.edit', $tipo) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <form method="POST" action="{{ route('tipos-licencia.destroy', $tipo) }}"
              onsubmit="return confirm('¿Eliminar «{{ addslashes($tipo->nombre) }}»?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash me-1"></i> Eliminar
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos generales
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">Nombre</dt>
                    <dd class="col-sm-7">{{ $tipo->nombre }}</dd>

                    <dt class="col-sm-5">Descripción</dt>
                    <dd class="col-sm-7">{{ $tipo->descripcion ?? '—' }}</dd>

                    <dt class="col-sm-5">Cómputo</dt>
                    <dd class="col-sm-7">
                        {{ $tipo->esDiasCorridos() ? 'Días corridos' : 'Días hábiles' }}
                    </dd>

                    <dt class="col-sm-5">Afecta</dt>
                    <dd class="col-sm-7">
                        {{ $tipo->afectaUsuario() ? 'Usuario' : 'Designación' }}
                    </dd>

                    <dt class="col-sm-5">Días máximos</dt>
                    <dd class="col-sm-7">{{ $tipo->dias_maximos ?? 'Sin límite' }}</dd>

                    <dt class="col-sm-5">Requiere doc.</dt>
                    <dd class="col-sm-7">
                        @if($tipo->requiere_documentacion)
                            <span class="badge bg-warning text-dark">Sí</span>
                        @else
                            <span class="badge bg-light text-dark">No</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5">Institución</dt>
                    <dd class="col-sm-7">
                        @if($tipo->institucion)
                            <span title="También visible en instituciones hijas">
                                <i class="bi bi-building me-1 text-secondary"></i>
                                {{ $tipo->institucion->nombre }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Global</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5">Estado</dt>
                    <dd class="col-sm-7">
                        @if($tipo->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-file-earmark-medical me-1"></i> Licencias registradas
                <span class="badge bg-light text-dark ms-auto">{{ $tipo->licencias()->count() }}</span>
            </div>
            <div class="card-body p-0">
                @php $licencias = $tipo->licencias()->with('usuario')->orderByDesc('fecha_inicio')->limit(10)->get(); @endphp
                @if($licencias->isEmpty())
                    <p class="text-muted text-center py-4 mb-0 small">
                        <i class="bi bi-inbox d-block fs-4 mb-1"></i> Sin licencias registradas.
                    </p>
                @else
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Usuario</th>
                                <th class="small">Desde</th>
                                <th class="small">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($licencias as $lic)
                                <tr>
                                    <td class="small">{{ $lic->usuario->apellidos ?? '—' }}</td>
                                    <td class="small font-monospace">{{ $lic->fecha_inicio }}</td>
                                    <td>
                                        @php
                                            $badgeMap = ['pendiente'=>'warning','aprobada'=>'success','rechazada'=>'danger'];
                                            $color = $badgeMap[$lic->estado] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }} text-{{ $color === 'warning' ? 'dark' : 'white' }}">
                                            {{ ucfirst($lic->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
