@extends('layouts.app')

@section('title', $usuario->nombre_completo)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">{{ $usuario->nombre_completo }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-person-circle me-1"></i> Perfil de Usuario
    </h5>
    <div>
        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Columna izquierda: datos del usuario --}}
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-person me-1"></i> Datos personales
            </div>
            <div class="card-body">
                {{-- Avatar --}}
                <div class="mb-3">
                    @if($usuario->foto)
                        <img src="{{ asset('storage/' . $usuario->foto) }}" alt="{{ $usuario->nombre_completo }}"
                             style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--celeste)">
                    @else
                        <div style="width:90px;height:90px;border-radius:50%;background:var(--azul);
                                    color:#fff;display:flex;align-items:center;justify-content:center;
                                    font-weight:700;font-size:2rem;margin:0 auto;border:3px solid var(--celeste)">
                            {{ strtoupper(substr($usuario->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($usuario->apellidos ?? '', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <h6 class="fw-bold mb-1">{{ $usuario->nombre_completo }}</h6>

                <dl class="row text-start mt-3 mb-0 small">
                    <dt class="col-5 text-muted">Documento</dt>
                    <dd class="col-7">{{ $usuario->documento ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Email</dt>
                    <dd class="col-7 text-break">
                        @if($usuario->email)
                            <a href="mailto:{{ $usuario->email }}" class="text-decoration-none">{{ $usuario->email }}</a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Sexo</dt>
                    <dd class="col-7">
                        @php
                            $sexoMap = ['M' => 'Masculino', 'F' => 'Femenino', 'X' => 'No binario'];
                        @endphp
                        {{ $sexoMap[$usuario->sexo] ?? '—' }}
                    </dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        @if($usuario->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Columna derecha: tabs --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-card-list me-1"></i> Información laboral
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="usuarioTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active small" id="tab-designaciones" data-bs-toggle="tab"
                                data-bs-target="#pane-designaciones" type="button" role="tab">
                            <i class="bi bi-briefcase me-1"></i> Designaciones activas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link small" id="tab-roles" data-bs-toggle="tab"
                                data-bs-target="#pane-roles" type="button" role="tab">
                            <i class="bi bi-shield-check me-1"></i> Roles
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3">
                    {{-- Designaciones --}}
                    <div class="tab-pane fade show active" id="pane-designaciones" role="tabpanel">
                        @if($usuario->designaciones->isEmpty())
                            <p class="text-muted text-center py-3 mb-0 small">
                                <i class="bi bi-briefcase fs-4 d-block mb-1"></i>
                                Sin designaciones activas.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cargo</th>
                                            <th>Institución</th>
                                            <th>Dependencia</th>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usuario->designaciones as $des)
                                            <tr>
                                                <td>{{ $des->cargo->nombre ?? '—' }}</td>
                                                <td>{{ $des->institucion->nombre ?? '—' }}</td>
                                                <td>{{ $des->dependencia->nombre ?? '—' }}</td>
                                                <td class="small">{{ $des->fecha_inicio ? \Carbon\Carbon::parse($des->fecha_inicio)->format('d/m/Y') : '—' }}</td>
                                                <td class="small">{{ $des->fecha_fin ? \Carbon\Carbon::parse($des->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Roles --}}
                    <div class="tab-pane fade" id="pane-roles" role="tabpanel">
                        <p class="fw-semibold small text-muted mb-1">Roles globales</p>
                        @php $rolesGlobal = $usuario->getRoleNames(); @endphp
                        @if($rolesGlobal->isEmpty())
                            <p class="text-muted small mb-3">Sin roles globales asignados.</p>
                        @else
                            <div class="mb-3">
                                @foreach($rolesGlobal as $rol)
                                    <span class="badge me-1 mb-1" style="background:var(--azul)">{{ $rol }}</span>
                                @endforeach
                            </div>
                        @endif

                        <p class="fw-semibold small text-muted mb-1">Roles por institución</p>
                        @if($usuario->rolesInstitucion->isEmpty())
                            <p class="text-muted small mb-0">Sin roles institucionales asignados.</p>
                        @else
                            @foreach($usuario->rolesInstitucion as $ri)
                                <div class="d-flex align-items-center gap-2 mb-1 small">
                                    <span class="badge" style="background:var(--celeste);color:#fff">{{ $ri->rolInstitucion->nombre ?? '—' }}</span>
                                    <span class="text-muted">{{ $ri->institucion->nombre ?? '' }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
