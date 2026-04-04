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

                    @if($usuario->telefono)
                    <dt class="col-5 text-muted">Teléfono</dt>
                    <dd class="col-7">{{ $usuario->telefono }}</dd>
                    @endif

                    @if($usuario->domicilio || $usuario->ciudadDomicilio)
                    <dt class="col-5 text-muted">Domicilio</dt>
                    <dd class="col-7">
                        {{ $usuario->domicilio }}
                        @if($usuario->ciudadDomicilio)
                            <span class="text-muted small d-block">
                                {{ $usuario->ciudadDomicilio->nombre }},
                                {{ $usuario->ciudadDomicilio->estado?->nombre }}
                            </span>
                        @endif
                    </dd>
                    @endif

                    <dt class="col-5 text-muted">Sexo</dt>
                    <dd class="col-7">
                        @php
                            $sexoMap = ['M' => 'Masculino', 'F' => 'Femenino', 'X' => 'No binario'];
                        @endphp
                        {{ $sexoMap[$usuario->sexo] ?? '—' }}
                    </dd>

                    @if($usuario->paisNacimiento || $usuario->estadoNacimiento)
                    <dt class="col-5 text-muted">Nacimiento</dt>
                    <dd class="col-7 small">
                        {{ $usuario->estadoNacimiento?->nombre ?? '' }}
                        @if($usuario->paisNacimiento)
                            <span class="text-muted">({{ $usuario->paisNacimiento->nombre }})</span>
                        @endif
                    </dd>
                    @endif

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

                        {{-- ── Roles globales ───────────────────────────────── --}}
                        @if($esAdminGeneral)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="fw-semibold small text-muted mb-0">Roles globales</p>
                            </div>
                            @php $rolesGlobal = $usuario->getRoleNames(); @endphp
                            <div class="mb-2 d-flex flex-wrap gap-1 align-items-center">
                                @forelse($rolesGlobal as $rg)
                                    <span class="badge d-inline-flex align-items-center gap-1" style="background:var(--azul)">
                                        {{ $rg }}
                                        <form method="POST" action="{{ route('usuarios.roles.global.destroy', $usuario) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Revocar rol global «{{ addslashes($rg) }}»?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="rol" value="{{ $rg }}">
                                            <button type="submit" class="btn-close btn-close-white ms-1"
                                                    style="font-size:.55rem;padding:.2rem" title="Revocar"></button>
                                        </form>
                                    </span>
                                @empty
                                    <span class="text-muted small">Sin roles globales.</span>
                                @endforelse

                                <form method="POST" action="{{ route('usuarios.roles.global.store', $usuario) }}"
                                      class="d-flex gap-1 ms-2">
                                    @csrf
                                    <select name="rol" class="form-select form-select-sm" style="width:auto" required>
                                        <option value="">+ Asignar rol global…</option>
                                        @foreach($rolesGlobales as $rg)
                                            @if(!$rolesGlobal->contains($rg->name))
                                                <option value="{{ $rg->name }}">{{ $rg->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </form>
                            </div>
                            <hr class="my-2">
                        @endif

                        {{-- ── Roles institucionales ────────────────────────── --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <p class="fw-semibold small text-muted mb-0">Roles institucionales</p>
                            @if($puedeGestionar)
                                <button class="btn btn-sm" style="background:var(--azul);color:#fff"
                                        data-bs-toggle="modal" data-bs-target="#modal-asignar-rol">
                                    <i class="bi bi-plus-lg me-1"></i> Asignar rol
                                </button>
                            @endif
                        </div>

                        @if($usuario->rolesInstitucion->isEmpty())
                            <p class="text-muted small text-center py-3">
                                <i class="bi bi-shield-slash d-block mb-1 fs-5"></i>
                                Sin roles institucionales asignados.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rol</th>
                                            <th>Institución</th>
                                            <th class="text-center">Desde</th>
                                            <th class="text-center">Hasta</th>
                                            <th class="text-center">Estado</th>
                                            @if($puedeGestionar)<th class="text-center" style="width:80px">Acciones</th>@endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usuario->rolesInstitucion as $ri)
                                            @php
                                                $rolNombre   = $ri->rolInstitucion?->nombre ?? '';
                                                $nivelRol    = $ri->rolInstitucion?->nivel ?? 999;
                                                // Puede gestionar solo roles con nivel estrictamente mayor al propio
                                                $puedeEditar = $puedeGestionar
                                                    && ($nivelActor === 0 || $nivelRol > $nivelActor);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge" style="background:var(--celeste)">{{ $rolNombre ?: '—' }}</span>
                                                    @if(!$puedeEditar && $puedeGestionar)
                                                        <i class="bi bi-lock-fill text-muted ms-1 small" title="Rol de jerarquía superior — no modificable"></i>
                                                    @endif
                                                </td>
                                                <td>{{ $ri->institucion?->nombre ?? '—' }}</td>
                                                <td class="text-center">{{ $ri->fecha_desde?->format('d/m/Y') ?? '—' }}</td>
                                                <td class="text-center">{{ $ri->fecha_hasta?->format('d/m/Y') ?? '∞' }}</td>
                                                <td class="text-center">
                                                    @if($ri->estaVigente())
                                                        <span class="badge bg-success">Vigente</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactiva</span>
                                                    @endif
                                                </td>
                                                @if($puedeGestionar)
                                                    <td class="text-center">
                                                        @if($puedeEditar)
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary py-0 px-1 btn-editar-rol"
                                                                    title="Editar"
                                                                    data-id="{{ $ri->id }}"
                                                                    data-rol="{{ $ri->id_rol_institucion }}"
                                                                    data-inst="{{ $ri->id_institucion }}"
                                                                    data-desde="{{ $ri->fecha_desde?->format('Y-m-d') }}"
                                                                    data-hasta="{{ $ri->fecha_hasta?->format('Y-m-d') }}"
                                                                    data-activo="{{ $ri->activo ? '1' : '0' }}">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form method="POST"
                                                                  action="{{ route('usuarios.roles.destroy', $ri) }}"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('¿Revocar rol «{{ addslashes($rolNombre) }}»?')">
                                                                @csrf @method('DELETE')
                                                                <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Revocar">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($puedeGestionar)
{{-- ══ Modal: Asignar rol ══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-asignar-rol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('usuarios.roles.store', $usuario) }}">
                @csrf
                <div class="modal-header" style="background:var(--azul);color:#fff">
                    <h6 class="modal-title"><i class="bi bi-shield-plus me-1"></i> Asignar rol institucional</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('usuarios._form_rol', ['asignacion' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                        <i class="bi bi-check-lg me-1"></i> Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ Modal: Editar rol ═══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-editar-rol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="form-editar-rol" action="">
                @csrf @method('PUT')
                <div class="modal-header" style="background:var(--azul);color:#fff">
                    <h6 class="modal-title"><i class="bi bi-shield-check me-1"></i> Editar asignación de rol</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('usuarios._form_rol', ['asignacion' => null, 'edicion' => true])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                        <i class="bi bi-check-lg me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-editar-rol').forEach(btn => {
    btn.addEventListener('click', function () {
        const id     = this.dataset.id;
        const form   = document.getElementById('form-editar-rol');
        form.action  = '{{ url("usuarios/roles") }}/' + id;

        form.querySelector('[name="id_rol_institucion"]').value = this.dataset.rol;
        form.querySelector('[name="fecha_desde"]').value        = this.dataset.desde;
        form.querySelector('[name="fecha_hasta"]').value        = this.dataset.hasta ?? '';
        form.querySelector('[name="activo"][value="1"]').checked = this.dataset.activo === '1';

        const instSelect = form.querySelector('[name="id_institucion"]');
        if (instSelect) instSelect.value = this.dataset.inst;

        new bootstrap.Modal(document.getElementById('modal-editar-rol')).show();
    });
});
</script>
@endpush
@endif
@endsection
