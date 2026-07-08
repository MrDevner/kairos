@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mi Perfil</li>
@endsection

@section('content')

@php
    $sexoMap = ['M' => 'Masculino', 'F' => 'Femenino', 'X' => 'No binario'];

    $camposDatos     = ['apellidos','nombres','documento','sexo','id_pais_nacimiento','id_estado_nacimiento',
                         'email','telefono','domicilio','id_ciudad_domicilio','foto'];
    $camposSeguridad = ['password','password_confirmation','pin_marca','pin_marca_confirmation'];

    $tabActiva = 'pane-datos';
    if ($errors->hasAny($camposSeguridad)) {
        $tabActiva = 'pane-seguridad';
    } elseif ($errors->hasAny($camposDatos)) {
        $tabActiva = 'pane-datos';
    }
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

@if(session('success') && !str_contains(session('success'), 'PIN'))
    <div class="alert alert-success alert-dismissible fade show py-2 small mb-3">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 small mb-3">
        {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card">
        <ul class="nav nav-tabs px-3 pt-2" id="perfilTabsTop" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $tabActiva === 'pane-datos' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-datos" type="button">
                    <i class="bi bi-person me-1"></i> Datos personales
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-designaciones" type="button">
                    <i class="bi bi-briefcase me-1"></i> Designaciones
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-licencias" type="button">
                    <i class="bi bi-calendar-check me-1"></i> Licencias
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-informes" type="button">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Informes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $tabActiva === 'pane-seguridad' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-seguridad" type="button">
                    <i class="bi bi-shield-lock me-1"></i> Seguridad
                </button>
            </li>
        </ul>

        <div class="card-body">
            <div class="tab-content">

                {{-- ══════════════════════════════════════════════════════════
                     DATOS PERSONALES (personales + contacto + foto)
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ $tabActiva === 'pane-datos' ? 'show active' : '' }}" id="pane-datos" role="tabpanel">
                    <div class="row g-3">

                        {{-- Foto de perfil --}}
                        <div class="col-lg-3">
                            <div class="card h-100">
                                <div class="card-header" style="background:var(--azul);color:#fff">
                                    <i class="bi bi-image me-1"></i> Foto de perfil
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
                        </div>

                        <div class="col-lg-9">
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

                            {{-- Datos de contacto --}}
                            <div class="card">
                                <div class="card-header" style="background:var(--azul);color:#fff">
                                    <i class="bi bi-telephone me-1"></i> Datos de contacto
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Email</label>
                                            <input type="email" name="email"
                                                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                                                   value="{{ old('email', $usuario->email) }}" maxlength="150">
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Teléfono</label>
                                            <input type="text" name="telefono"
                                                   class="form-control form-control-sm @error('telefono') is-invalid @enderror"
                                                   value="{{ old('telefono', $usuario->telefono) }}" maxlength="30"
                                                   placeholder="Ej: 264-1234567">
                                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Domicilio</label>
                                            <input type="text" name="domicilio"
                                                   class="form-control form-control-sm @error('domicilio') is-invalid @enderror"
                                                   value="{{ old('domicilio', $usuario->domicilio) }}" maxlength="255"
                                                   placeholder="Calle, número, ciudad">
                                            @error('domicilio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        {{-- Departamento del domicilio --}}
                                        <div class="col-12">
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
                                                'colPais'         => 'col-md-4',
                                                'colEstado'       => 'col-md-4',
                                                'colCiudad'       => 'col-md-4',
                                                'readonly'        => false,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     DESIGNACIONES
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="pane-designaciones" role="tabpanel">
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

                {{-- ══════════════════════════════════════════════════════════
                     LICENCIAS
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="pane-licencias" role="tabpanel">
                    @if($licencias->isEmpty())
                        <p class="text-muted text-center py-3 mb-0 small">
                            <i class="bi bi-calendar-check fs-4 d-block mb-1"></i>
                            Sin licencias registradas.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th class="text-center">Desde</th>
                                        <th class="text-center">Hasta</th>
                                        <th class="text-center">Días</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($licencias as $lic)
                                        @php
                                            $badgeColor = ['pendiente'=>'warning','aprobada'=>'success','rechazada'=>'danger'][$lic->estado] ?? 'secondary';
                                            $textColor  = $lic->estado === 'pendiente' ? 'dark' : 'white';
                                        @endphp
                                        <tr>
                                            <td>{{ $lic->tipoLicencia?->nombre ?? '—' }}</td>
                                            <td class="text-center">{{ $lic->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-center">{{ $lic->fecha_fin?->format('d/m/Y') ?? '∞' }}</td>
                                            <td class="text-center">{{ $lic->dias_computados ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $badgeColor }} text-{{ $textColor }}">
                                                    {{ ucfirst($lic->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($licencias->count() >= 20)
                            <p class="text-muted small mt-2 mb-0">Mostrando las 20 licencias más recientes.</p>
                        @endif
                    @endif
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     INFORMES
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="pane-informes" role="tabpanel">
                    <p class="text-muted text-center py-4 mb-0 small">
                        <i class="bi bi-file-earmark-bar-graph fs-4 d-block mb-1"></i>
                        Próximamente.
                    </p>
                </div>

                {{-- ══════════════════════════════════════════════════════════
                     SEGURIDAD (contraseña + PIN + métodos de acceso + roles)
                ═══════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ $tabActiva === 'pane-seguridad' ? 'show active' : '' }}" id="pane-seguridad" role="tabpanel">
                    <div class="row g-3">

                        <div class="col-lg-6">
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

                            {{-- PIN de marca --}}
                            <div class="card">
                                <div class="card-header" style="background:var(--azul);color:#fff">
                                    <i class="bi bi-123 me-1"></i> PIN de marca
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        PIN numérico de 4 dígitos para registrar marcas en los terminales web.
                                        Dejá en blanco para no cambiar.
                                    </p>
                                    @if(session('success') && str_contains(session('success'), 'PIN'))
                                        <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Nuevo PIN</label>
                                        <input type="password"
                                               name="pin_marca"
                                               class="form-control form-control-sm @error('pin_marca') is-invalid @enderror"
                                               inputmode="numeric"
                                               maxlength="4"
                                               placeholder="4 dígitos"
                                               autocomplete="off">
                                        @error('pin_marca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label fw-semibold small">Confirmar PIN</label>
                                        <input type="password"
                                               name="pin_marca_confirmation"
                                               class="form-control form-control-sm"
                                               inputmode="numeric"
                                               maxlength="4"
                                               placeholder="Repetir PIN"
                                               autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            {{-- Métodos de acceso --}}
                            <div class="card mb-3" id="metodos-login">
                                <div class="card-header" style="background:var(--azul);color:#fff">
                                    <i class="bi bi-key me-1"></i> Métodos de acceso
                                </div>
                                <div class="card-body">
                                    {{-- Contraseña --}}
                                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="small">
                                            <i class="bi bi-shield-lock me-1 text-secondary"></i>
                                            <span class="fw-semibold">Contraseña del sistema</span>
                                        </div>
                                        @if($usuario->tienePassword())
                                            <span class="badge bg-success">Configurada</span>
                                        @else
                                            <span class="badge bg-secondary">No configurada</span>
                                        @endif
                                    </div>

                                    {{-- PIN de marca --}}
                                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="small">
                                            <i class="bi bi-123 me-1 text-secondary"></i>
                                            <span class="fw-semibold">PIN de marca</span>
                                            <div class="text-muted" style="font-size:.72rem">Terminales web</div>
                                        </div>
                                        @if($usuario->tienePinMarca())
                                            <span class="badge bg-success">Configurado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Sin configurar</span>
                                        @endif
                                    </div>

                                    {{-- Google --}}
                                    <div class="d-flex align-items-center justify-content-between py-2">
                                        <div class="small">
                                            <i class="bi bi-google me-1 text-secondary"></i>
                                            <span class="fw-semibold">Google</span>
                                            @if($usuario->google_id)
                                                <div class="text-muted" style="font-size:.75rem">{{ $usuario->email }}</div>
                                            @endif
                                        </div>
                                        @if($usuario->google_id)
                                            @if($usuario->password)
                                                <form method="POST" action="{{ route('perfil.google.desvincular') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                                            onclick="return confirm('¿Desvincular tu cuenta de Google?')">
                                                        Desvincular
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-success">Vinculado</span>
                                            @endif
                                        @else
                                            <a href="{{ route('perfil.google.vincular') }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                                Vincular
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Roles --}}
                            <div class="card">
                                <div class="card-header" style="background:var(--azul);color:#fff">
                                    <i class="bi bi-shield-check me-1"></i> Roles
                                </div>
                                <div class="card-body">
                                    @php $rolesGlobal = $usuario->nombresRolesGlobales(); @endphp

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
                </div>

            </div>{{-- /tab-content --}}
        </div>{{-- /card-body --}}
    </div>{{-- /card --}}

    <button type="submit" class="btn mt-3" style="background:var(--azul);color:#fff">
        <i class="bi bi-check-lg me-1"></i> Guardar cambios
    </button>
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

document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#metodos-login') {
        const tabBtn = document.querySelector('[data-bs-target="#pane-seguridad"]');
        if (tabBtn) {
            bootstrap.Tab.getOrCreateInstance(tabBtn).show();
            setTimeout(function () {
                document.getElementById('metodos-login')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }
});
</script>
@endpush
