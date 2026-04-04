@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mi Perfil</li>
@endsection

@section('content')

@php
    $sexoMap = ['M' => 'Masculino', 'F' => 'Femenino', 'X' => 'No binario'];
@endphp

<div class="d-flex align-items-center mb-3 gap-2">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-person-circle me-1"></i> Mi Perfil
    </h5>
    @if($puedeEditarTodo)
        <span class="badge" style="background:var(--celeste);color:var(--azul);font-size:.75rem">
            <i class="bi bi-shield-check me-1"></i> Edición completa
        </span>
    @endif
</div>

<form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

<div class="row g-3">

    {{-- ══ Columna izquierda: avatar + contacto + seguridad ══════════════════ --}}
    <div class="col-lg-4">

        {{-- Avatar --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-person-circle me-1"></i> Foto de perfil
            </div>
            <div class="card-body text-center">
                <div id="preview-container" class="mb-3 mx-auto"
                     style="width:100px;height:100px;border-radius:50%;overflow:hidden;
                            border:3px solid var(--celeste);background:var(--azul);
                            display:flex;align-items:center;justify-content:center;">
                    @if($usuario->foto)
                        <img id="preview-img" src="{{ asset('storage/' . $usuario->foto) }}"
                             alt="" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <span id="preview-initials" style="color:#fff;font-weight:700;font-size:2rem">
                            {{ strtoupper(substr($usuario->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr($usuario->apellidos ?? '', 0, 1)) }}
                        </span>
                        <img id="preview-img" src="#" alt=""
                             style="display:none;width:100%;height:100%;object-fit:cover">
                    @endif
                </div>
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="form-control form-control-sm @error('foto') is-invalid @enderror">
                <div class="form-text small mt-1">JPG, PNG. Máx. 2 MB.</div>
                @error('foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Datos de contacto --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-telephone me-1"></i> Datos de contacto
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Email</label>
                    <input type="email" name="email"
                           class="form-control form-control-sm @error('email') is-invalid @enderror"
                           value="{{ old('email', $usuario->email) }}" maxlength="150">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Teléfono</label>
                    <input type="text" name="telefono"
                           class="form-control form-control-sm @error('telefono') is-invalid @enderror"
                           value="{{ old('telefono', $usuario->telefono) }}" maxlength="30"
                           placeholder="Ej: 264-1234567">
                    @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Domicilio</label>
                    <input type="text" name="domicilio"
                           class="form-control form-control-sm @error('domicilio') is-invalid @enderror"
                           value="{{ old('domicilio', $usuario->domicilio) }}" maxlength="255"
                           placeholder="Calle, número, ciudad">
                    @error('domicilio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                {{-- Departamento del domicilio --}}
                <div class="mb-0">
                    <label class="form-label fw-semibold small">Departamento del domicilio</label>
                    @include('partials._ubicacion_select', [
                        'paises'          => $paises,
                        'prefijo'         => 'dom',
                        'labelPais'       => 'País del domicilio',
                        'labelEstado'     => 'Provincia',
                        'labelCiudad'     => 'Departamento',
                        'namePais'        => 'id_pais_domicilio',
                        'nameEstado'      => 'id_estado_domicilio',
                        'nameCiudad'      => 'id_ciudad_domicilio',
                        'idPaisActual'    => $usuario->ciudadDomicilio?->estado?->id_pais ?? $argentina,
                        'idEstadoActual'  => $usuario->ciudadDomicilio?->id_estado ?? $sanjuan,
                        'idCiudadActual'  => $usuario->id_ciudad_domicilio,
                        'colPais'         => 'col-12',
                        'colEstado'       => 'col-12',
                        'colCiudad'       => 'col-12',
                        'readonly'        => false,
                    ])
                </div>
            </div>
        </div>

        {{-- Contraseña --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-lock me-1"></i> Cambiar contraseña
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Dejá en blanco para no cambiar.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Nueva contraseña</label>
                    <input type="password" name="password" id="password"
                           class="form-control form-control-sm @error('password') is-invalid @enderror"
                           placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold small">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation"
                           class="form-control form-control-sm"
                           placeholder="Repetir contraseña" autocomplete="new-password">
                </div>
            </div>
        </div>

        <button type="submit" class="btn w-100" style="background:var(--azul);color:#fff">
            <i class="bi bi-check-lg me-1"></i> Guardar cambios
        </button>
    </div>

    {{-- ══ Columna derecha: datos personales + info laboral ══════════════════ --}}
    <div class="col-lg-8">

        {{-- Datos personales --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between"
                 style="background:var(--azul);color:#fff">
                <span><i class="bi bi-person me-1"></i> Datos personales</span>
                @if(!$puedeEditarTodo)
                    <span class="badge bg-secondary" style="font-size:.72rem">
                        <i class="bi bi-lock me-1"></i> Solo lectura
                    </span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Apellidos @if($puedeEditarTodo)<span class="text-danger">*</span>@endif
                        </label>
                        <input type="text" name="apellidos"
                               class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                               value="{{ old('apellidos', $usuario->apellidos) }}"
                               maxlength="100"
                               @if(!$puedeEditarTodo) readonly @endif>
                        @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Nombres @if($puedeEditarTodo)<span class="text-danger">*</span>@endif
                        </label>
                        <input type="text" name="nombres"
                               class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                               value="{{ old('nombres', $usuario->nombres) }}"
                               maxlength="100"
                               @if(!$puedeEditarTodo) readonly @endif>
                        @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">
                            Documento @if($puedeEditarTodo)<span class="text-danger">*</span>@endif
                        </label>
                        <input type="text" name="documento"
                               class="form-control form-control-sm @error('documento') is-invalid @enderror"
                               value="{{ old('documento', $usuario->documento) }}"
                               maxlength="20"
                               @if(!$puedeEditarTodo) readonly @endif>
                        @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Sexo</label>
                        @if($puedeEditarTodo)
                            <select name="sexo" class="form-select form-select-sm @error('sexo') is-invalid @enderror">
                                <option value="">— No especificado —</option>
                                <option value="M" @selected(old('sexo', $usuario->sexo) === 'M')>Masculino</option>
                                <option value="F" @selected(old('sexo', $usuario->sexo) === 'F')>Femenino</option>
                                <option value="X" @selected(old('sexo', $usuario->sexo) === 'X')>No binario</option>
                            </select>
                            @error('sexo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @else
                            <input type="text" class="form-control form-control-sm"
                                   value="{{ $sexoMap[$usuario->sexo] ?? 'No especificado' }}" readonly>
                        @endif
                    </div>
                    <div class="col-md-3 d-flex align-items-end pb-1">
                        <span class="badge {{ $usuario->activo ? 'bg-success' : 'bg-secondary' }} fs-6">
                            <i class="bi bi-{{ $usuario->activo ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    {{-- Datos de nacimiento --}}
                    @if($puedeEditarTodo)
                        @include('partials._ubicacion_select', [
                            'paises'          => $paises,
                            'prefijo'         => 'nac',
                            'labelPais'       => 'País de nacimiento',
                            'labelEstado'     => 'Provincia de nacimiento',
                            'labelCiudad'     => 'Departamento de nacimiento',
                            'namePais'        => 'id_pais_nacimiento',
                            'nameEstado'      => 'id_estado_nacimiento',
                            'nameCiudad'      => 'id_ciudad_nacimiento',
                            'idPaisActual'    => $usuario->id_pais_nacimiento,
                            'idEstadoActual'  => $usuario->id_estado_nacimiento,
                            'idCiudadActual'  => null,
                            'colPais'         => 'col-md-4',
                            'colEstado'       => 'col-md-4',
                            'colCiudad'       => 'col-md-4',
                            'readonly'        => false,
                        ])
                    @else
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">País de nacimiento</label>
                            <input class="form-control form-control-sm" readonly
                                   value="{{ $usuario->paisNacimiento?->nombre ?? '—' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Provincia de nacimiento</label>
                            <input class="form-control form-control-sm" readonly
                                   value="{{ $usuario->estadoNacimiento?->nombre ?? '—' }}">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info laboral: tabs --}}
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-briefcase me-1"></i> Información laboral
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="perfilTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active small" data-bs-toggle="tab"
                                data-bs-target="#pane-designaciones" type="button">
                            <i class="bi bi-briefcase me-1"></i> Designaciones
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link small" data-bs-toggle="tab"
                                data-bs-target="#pane-roles" type="button">
                            <i class="bi bi-shield-check me-1"></i> Roles
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3">

                    {{-- Designaciones vigentes --}}
                    <div class="tab-pane fade show active" id="pane-designaciones" role="tabpanel">
                        @if($usuario->designaciones->isEmpty())
                            <p class="text-muted text-center py-3 mb-0 small">
                                <i class="bi bi-briefcase fs-4 d-block mb-1"></i>
                                Sin designaciones vigentes.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cargo</th>
                                            <th>Institución</th>
                                            <th>Dependencia</th>
                                            <th class="text-center">Desde</th>
                                            <th class="text-center">Hasta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usuario->designaciones as $des)
                                            <tr>
                                                <td>{{ $des->cargo?->nombre ?? '—' }}</td>
                                                <td>{{ $des->institucion?->nombre ?? '—' }}</td>
                                                <td>{{ $des->dependencia?->nombre ?? '—' }}</td>
                                                <td class="text-center">
                                                    {{ $des->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $des->fecha_fin?->format('d/m/Y') ?? '∞' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Roles vigentes --}}
                    <div class="tab-pane fade" id="pane-roles" role="tabpanel">
                        @php $rolesGlobal = $usuario->getRoleNames(); @endphp

                        @if($rolesGlobal->isNotEmpty())
                            <p class="small fw-semibold text-muted mb-1">Roles globales</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach($rolesGlobal as $rg)
                                    <span class="badge" style="background:var(--azul)">{{ $rg }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($usuario->rolesInstitucion->isEmpty())
                            <p class="text-muted text-center py-2 small mb-0">
                                <i class="bi bi-shield-slash d-block mb-1 fs-5"></i>
                                Sin roles institucionales vigentes.
                            </p>
                        @else
                            <p class="small fw-semibold text-muted mb-1">Roles institucionales</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rol</th>
                                            <th>Institución</th>
                                            <th class="text-center">Desde</th>
                                            <th class="text-center">Hasta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usuario->rolesInstitucion as $ri)
                                            <tr>
                                                <td>
                                                    <span class="badge" style="background:var(--celeste);color:var(--azul)">
                                                        {{ $ri->rolInstitucion?->nombre ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>{{ $ri->institucion?->nombre ?? '—' }}</td>
                                                <td class="text-center">
                                                    {{ $ri->fecha_desde?->format('d/m/Y') ?? '—' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $ri->fecha_hasta?->format('d/m/Y') ?? '∞' }}
                                                </td>
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

    </div>{{-- /col-lg-8 --}}
</div>{{-- /row --}}
</form>

@endsection

@push('scripts')
<script>
document.getElementById('foto').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const initials = document.getElementById('preview-initials');
        if (initials) initials.style.display = 'none';
        const img = document.getElementById('preview-img');
        img.src = e.target.result;
        img.style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
