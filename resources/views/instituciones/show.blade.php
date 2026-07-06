@extends('layouts.app')
@section('title', $institucion->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('instituciones.index') }}">Instituciones</a></li>
    <li class="breadcrumb-item active">{{ $institucion->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-building me-1"></i> {{ $institucion->nombre }}
        @if($institucion->sigla)
            <small class="badge bg-secondary fw-normal ms-1">{{ $institucion->sigla }}</small>
        @endif
    </h5>
    <div>
        @if(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos())
            <a href="{{ route('instituciones.edit', $institucion) }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
        @endif
        <a href="{{ route('instituciones.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Columna izquierda --}}
    <div class="col-md-5">

        {{-- Datos generales --}}
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Datos generales
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Sigla</dt>
                    <dd class="col-7">{{ $institucion->sigla ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Institución padre</dt>
                    <dd class="col-7">
                        @if($institucion->padre)
                            <a href="{{ route('instituciones.show', $institucion->padre) }}" class="text-decoration-none">
                                {{ $institucion->padre->nombre }}
                            </a>
                        @else
                            <span class="text-muted">— (raíz)</span>
                        @endif
                    </dd>

                    @if($institucion->descripcion)
                        <dt class="col-5 text-muted">Descripción</dt>
                        <dd class="col-7">{{ $institucion->descripcion }}</dd>
                    @endif

                    @if($institucion->direccion)
                        <dt class="col-5 text-muted">Dirección</dt>
                        <dd class="col-7">{{ $institucion->direccion }}</dd>
                    @endif

                    @if($institucion->telefono)
                        <dt class="col-5 text-muted">Teléfono</dt>
                        <dd class="col-7">{{ $institucion->telefono }}</dd>
                    @endif

                    @if($institucion->email)
                        <dt class="col-5 text-muted">Email</dt>
                        <dd class="col-7 text-break">
                            <a href="mailto:{{ $institucion->email }}" class="text-decoration-none">
                                {{ $institucion->email }}
                            </a>
                        </dd>
                    @endif

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $institucion->activa ? 'success' : 'secondary' }}">
                            {{ $institucion->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Configuración --}}
        <div class="card mt-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-sliders me-1"></i> Configuración operativa
            </div>
            <div class="card-body">
                @php $cfg = $institucion->configuracion; @endphp
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted">Umbral mín. de jornada</dt>
                    <dd class="col-5">{{ $cfg['umbral_jornada_minima'] }} min</dd>

                    <dt class="col-7 text-muted">Banco de horas por</dt>
                    <dd class="col-5">{{ ucfirst($cfg['banco_horas_por']) }}</dd>

                    <dt class="col-7 text-muted">Avisos de usuario</dt>
                    <dd class="col-5">
                        @if($cfg['permite_avisos_usuario'])
                            <span class="badge bg-success">Habilitado</span>
                        @else
                            <span class="badge bg-secondary">Deshabilitado</span>
                        @endif
                    </dd>

                    <dt class="col-7 text-muted">Horas extra</dt>
                    <dd class="col-5">
                        @if($cfg['horas_extra_autorizadas'])
                            <span class="badge bg-success">Autorizadas</span>
                        @else
                            <span class="badge bg-secondary">No autorizadas</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Columna derecha --}}
    <div class="col-md-7">

        {{-- Sub-instituciones --}}
        <div class="card">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-diagram-3 me-1"></i> Sub-instituciones
                <span class="badge bg-light text-dark ms-auto">{{ $institucion->hijas->count() }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($institucion->hijas as $h)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <a href="{{ route('instituciones.show', $h) }}" class="text-decoration-none fw-semibold">
                            {{ $h->nombre }}
                        </a>
                        <span class="badge bg-{{ $h->activa ? 'success' : 'secondary' }}">
                            {{ $h->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small py-3 text-center">
                        <i class="bi bi-inbox d-block mb-1 fs-5"></i>
                        Sin sub-instituciones
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Autorización de licencias --}}
        @php
            $rolesAdicionalesIds = $institucion->configuracion['roles_autorizan_licencias'] ?? [];
            $rolesDefault        = \App\Models\Institucion::ROLES_AUTORIZAN_DEFAULT;
            $rolesExtra          = $rolesInst->whereIn('id', $rolesAdicionalesIds);
            $rolesOpcionales     = $rolesInst->whereNotIn('nombre', $rolesDefault);
        @endphp
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-shield-check me-1"></i> Autorización de licencias
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2 small mb-3">
                        {{ session('success') }}
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <p class="small text-muted mb-2">
                    Roles que pueden aprobar o rechazar licencias en esta institución:
                </p>

                {{-- Roles fijos --}}
                <div class="mb-2 small">
                    <span class="fw-semibold me-1">Siempre:</span>
                    @foreach($rolesDefault as $nombre)
                        <span class="badge bg-secondary me-1">{{ $nombre }}</span>
                    @endforeach
                </div>

                {{-- Roles adicionales configurados --}}
                @if($rolesExtra->isNotEmpty())
                    <div class="mb-3 small">
                        <span class="fw-semibold me-1">Adicionales:</span>
                        @foreach($rolesExtra as $r)
                            <span class="badge bg-primary me-1">{{ $r->nombre }}</span>
                        @endforeach
                    </div>
                @endif

                @if(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos())
                    <form method="POST"
                          action="{{ route('instituciones.autorizadores-licencias', $institucion) }}">
                        @csrf
                        <div class="mb-2 small fw-semibold">Roles adicionales habilitados:</div>
                        @forelse($rolesOpcionales as $rol)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                       name="roles_autorizan_licencias[]"
                                       value="{{ $rol->id }}"
                                       id="auth-rol-{{ $rol->id }}"
                                       @checked(in_array($rol->id, $rolesAdicionalesIds))>
                                <label class="form-check-label small" for="auth-rol-{{ $rol->id }}">
                                    {{ $rol->nombre }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small">No hay otros roles disponibles.</p>
                        @endforelse
                        <button type="submit" class="btn btn-sm mt-2"
                                style="background:var(--azul);color:#fff">
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Tipos de licencia para avisos --}}
        @php
            $puedeConfigAviso = auth()->user()->permisos()->administrador()->tieneTodosLosPermisos()
                || (\App\Models\RolInstitucion::nivelMinimoDeUsuario(auth()->id(), $institucion->id) <= \App\Models\RolInstitucion::NIVEL_GESTION);
        @endphp
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-megaphone-fill me-1"></i> Licencias disponibles para avisos
                @if($tiposLicenciaAvisoIds->isEmpty())
                    <span class="badge bg-light text-dark ms-2 small fw-normal">Todas</span>
                @else
                    <span class="badge bg-warning text-dark ms-2 small fw-normal">{{ $tiposLicenciaAvisoIds->count() }} seleccionadas</span>
                @endif
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    Tipos de licencia que aparecen en el formulario de aviso de ausencia.
                    Si no se selecciona ninguno, se muestran todos los tipos visibles.
                </p>

                @if($tiposLicenciaAvisoIds->isNotEmpty())
                    <div class="mb-2 small">
                        <span class="fw-semibold me-1">Habilitados:</span>
                        @foreach($tiposLicenciaDisponibles->whereIn('id', $tiposLicenciaAvisoIds->all()) as $tl)
                            <span class="badge bg-primary me-1">{{ $tl->nombre }}</span>
                        @endforeach
                    </div>
                @endif

                @if($puedeConfigAviso)
                    <form method="POST"
                          action="{{ route('instituciones.aviso-licencias', $institucion) }}">
                        @csrf
                        <div class="mb-2 small fw-semibold">Seleccionar tipos permitidos:</div>
                        @forelse($tiposLicenciaDisponibles as $tl)
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="tipos_licencia_aviso[]"
                                       value="{{ $tl->id }}"
                                       id="aviso-tl-{{ $tl->id }}"
                                       @checked($tiposLicenciaAvisoIds->contains($tl->id))>
                                <label class="form-check-label small" for="aviso-tl-{{ $tl->id }}">
                                    {{ $tl->nombre }}
                                    @if($tl->id_institucion)
                                        <span class="text-muted">(propio)</span>
                                    @else
                                        <span class="text-muted">(global)</span>
                                    @endif
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small">No hay tipos de licencia disponibles.</p>
                        @endforelse
                        <div class="d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm"
                                    style="background:var(--azul);color:#fff">
                                <i class="bi bi-save me-1"></i> Guardar
                            </button>
                            @if($tiposLicenciaAvisoIds->isNotEmpty())
                                <button type="submit" name="tipos_licencia_aviso" value=""
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="document.querySelectorAll('[name=\'tipos_licencia_aviso[]\']').forEach(c=>c.checked=false)"
                                        title="Quitar restricción — mostrar todos">
                                    <i class="bi bi-x-circle me-1"></i> Sin restricción
                                </button>
                            @endif
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Dependencias --}}
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-diagram-2 me-1"></i> Dependencias
                <span class="badge bg-light text-dark ms-auto">{{ $institucion->dependencias->count() }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($institucion->dependencias->take(15) as $dep)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                        <a href="{{ route('dependencias.show', $dep) }}" class="text-decoration-none">
                            {{ $dep->nombre }}
                            @if($dep->sigla)
                                <span class="text-muted">({{ $dep->sigla }})</span>
                            @endif
                        </a>
                        <span class="badge bg-{{ $dep->activa ? 'success' : 'secondary' }}">
                            {{ $dep->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small py-3 text-center">
                        <i class="bi bi-inbox d-block mb-1 fs-5"></i>
                        Sin dependencias
                    </li>
                @endforelse
                @if($institucion->dependencias->count() > 15)
                    <li class="list-group-item text-muted small text-center py-2">
                        … y {{ $institucion->dependencias->count() - 15 }} más
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection
