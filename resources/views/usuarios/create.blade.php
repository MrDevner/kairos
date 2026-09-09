@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
@php
    // Si el envío previo falló por otro motivo (no por el documento), reabrimos
    // el paso 2 con los datos ya cargados en lugar de perder lo que el usuario escribió.
    $mostrarDatos      = old('paso') === '2';
    $documentoBloqueado = $mostrarDatos && ! $errors->has('documento');
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
    </h5>
    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person me-1"></i> Datos del usuario
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data" id="form-usuario">
            @csrf
            <input type="hidden" name="paso" id="input-paso" value="{{ $mostrarDatos ? '2' : '1' }}">

            {{-- ══ Paso 1: documento ══ --}}
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Número de documento <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="documento" id="documento"
                               class="form-control @error('documento') is-invalid @enderror"
                               value="{{ old('documento') }}" required maxlength="20"
                               {{ $documentoBloqueado ? 'readonly' : '' }}
                               autofocus>
                        <button type="button" id="btn-verificar-documento" class="btn btn-outline-primary"
                                style="{{ $documentoBloqueado ? 'display:none' : '' }}">
                            <i class="bi bi-search me-1"></i>Verificar
                        </button>
                        <button type="button" id="btn-cambiar-documento" class="btn btn-outline-secondary"
                                title="Cambiar documento"
                                style="{{ ! $documentoBloqueado ? 'display:none' : '' }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div id="documento-feedback" class="form-text"></div>
                </div>
            </div>

            <div id="alerta-documento-duplicado" class="alert alert-warning d-flex align-items-start mt-3 d-none">
                <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                <div id="alerta-documento-duplicado-texto"></div>
            </div>

            {{-- ══ Paso 2: datos personales (habilitado tras verificar el documento) ══ --}}
            <div id="seccion-datos" class="{{ $mostrarDatos ? '' : 'd-none' }}">
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" name="apellidos" id="apellidos" class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                               value="{{ old('apellidos') }}" required maxlength="100">
                        @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nombres <span class="text-danger">*</span></label>
                        <input type="text" name="nombres" class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                               value="{{ old('nombres') }}" required maxlength="100">
                        @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Sexo <span class="text-danger">*</span></label>
                        <select name="sexo" class="form-select form-select-sm @error('sexo') is-invalid @enderror" required>
                            <option value="" disabled @selected(!old('sexo'))>Seleccionar…</option>
                            <option value="M" @selected(old('sexo') === 'M')>Masculino</option>
                            <option value="F" @selected(old('sexo') === 'F')>Femenino</option>
                            <option value="X" @selected(old('sexo') === 'X')>No binario</option>
                        </select>
                        @error('sexo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" maxlength="150">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Fecha de nacimiento</label>
                        <input type="date" name="nacimiento"
                               class="form-control form-control-sm @error('nacimiento') is-invalid @enderror"
                               value="{{ old('nacimiento') }}">
                        @error('nacimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-1">
                            <input type="hidden" name="activo" value="0">
                            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                                   @checked(old('activo', 1))>
                            <label class="form-check-label small" for="activo">Usuario activo</label>
                        </div>
                    </div>

                    {{-- Contraseña --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Contraseña</label>
                        <input type="password" name="password" id="password"
                               class="form-control form-control-sm @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation"
                               class="form-control form-control-sm"
                               placeholder="Repetir contraseña" autocomplete="new-password">
                    </div>

                    {{-- Foto --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Foto de perfil</label>
                        <div class="d-flex align-items-center gap-3">
                            <div id="preview-container" style="width:70px;height:70px;border-radius:50%;
                                 background:var(--azul);color:#fff;display:flex;align-items:center;
                                 justify-content:center;font-size:1.5rem;overflow:hidden;flex-shrink:0">
                                <i class="bi bi-person" id="preview-icon"></i>
                                <img id="preview-img" src="#" alt="" style="display:none;width:100%;height:100%;object-fit:cover">
                            </div>
                            <div>
                                <input type="file" name="foto" id="foto" accept="image/*"
                                       class="form-control form-control-sm @error('foto') is-invalid @enderror">
                                <div class="form-text small">JPG, PNG o GIF. Máx. 2MB.</div>
                                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Roles --}}
                    @if(isset($roles) && $roles->count())
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Roles globales</label>
                        <div class="row g-1">
                            @foreach($roles as $rol)
                                <div class="col-sm-4 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]"
                                               id="rol_{{ $rol->id }}" value="{{ $rol->nombre }}"
                                               @checked(in_array($rol->nombre, old('roles', [])))>
                                        <label class="form-check-label small" for="rol_{{ $rol->id }}">
                                            {{ $rol->nombre }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
    const verificarUrl = "{{ route('usuarios.verificar-documento') }}";

    const inputDocumento = document.getElementById('documento');
    const btnVerificar    = document.getElementById('btn-verificar-documento');
    const btnCambiar      = document.getElementById('btn-cambiar-documento');
    const seccionDatos    = document.getElementById('seccion-datos');
    const feedback        = document.getElementById('documento-feedback');
    const alerta          = document.getElementById('alerta-documento-duplicado');
    const alertaTexto     = document.getElementById('alerta-documento-duplicado-texto');
    const inputPaso       = document.getElementById('input-paso');

    function habilitarDatos() {
        inputDocumento.readOnly = true;
        btnVerificar.style.display = 'none';
        btnCambiar.style.display = '';
        seccionDatos.classList.remove('d-none');
        alerta.classList.add('d-none');
        inputPaso.value = '2';
        document.getElementById('apellidos')?.focus();
    }

    function volverAPaso1() {
        inputDocumento.readOnly = false;
        btnVerificar.style.display = '';
        btnCambiar.style.display = 'none';
        seccionDatos.classList.add('d-none');
        inputPaso.value = '1';
        feedback.innerHTML = '';
        inputDocumento.focus();
        inputDocumento.select();
    }

    function verificarDocumento() {
        const documento = inputDocumento.value.trim();
        alerta.classList.add('d-none');
        feedback.innerHTML = '';
        inputDocumento.classList.remove('is-invalid');

        if (!documento) {
            inputDocumento.classList.add('is-invalid');
            feedback.innerHTML = '<span class="text-danger">Ingrese un número de documento.</span>';
            return;
        }

        btnVerificar.disabled = true;
        const textoOriginal = btnVerificar.innerHTML;
        btnVerificar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verificando…';

        fetch(verificarUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ documento }),
        })
            .then(r => r.json())
            .then(data => {
                if (data.disponible) {
                    feedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Documento disponible.</span>';
                    habilitarDatos();
                } else {
                    const u = data.usuario;
                    alertaTexto.innerHTML = 'El documento <strong>' + documento + '</strong> ya está registrado a nombre de ' +
                        '<strong>' + u.nombre + '</strong>' +
                        (u.activo ? '' : ' <span class="badge bg-secondary">Inactivo</span>') +
                        '. <a href="' + u.url + '" class="alert-link">Ver ficha del usuario</a> antes de continuar.';
                    alerta.classList.remove('d-none');
                    inputDocumento.classList.add('is-invalid');
                }
            })
            .catch(() => {
                feedback.innerHTML = '<span class="text-danger">No se pudo verificar el documento. Intente nuevamente.</span>';
            })
            .finally(() => {
                btnVerificar.disabled = false;
                btnVerificar.innerHTML = textoOriginal;
            });
    }

    btnVerificar?.addEventListener('click', verificarDocumento);
    btnCambiar?.addEventListener('click', volverAPaso1);

    inputDocumento?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && seccionDatos.classList.contains('d-none')) {
            e.preventDefault();
            verificarDocumento();
        }
    });

    document.getElementById('foto').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview-icon').style.display = 'none';
            const img = document.getElementById('preview-img');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
})();
</script>
@endpush
