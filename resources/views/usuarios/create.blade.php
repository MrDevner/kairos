@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
    </h5>
    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-person me-1"></i> Datos del usuario
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" name="apellidos" class="form-control form-control-sm @error('apellidos') is-invalid @enderror"
                           value="{{ old('apellidos') }}" required maxlength="100" autofocus>
                    @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Nombres <span class="text-danger">*</span></label>
                    <input type="text" name="nombres" class="form-control form-control-sm @error('nombres') is-invalid @enderror"
                           value="{{ old('nombres') }}" required maxlength="100">
                    @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Documento <span class="text-danger">*</span></label>
                    <input type="text" name="documento" class="form-control form-control-sm @error('documento') is-invalid @enderror"
                           value="{{ old('documento') }}" required maxlength="20">
                    @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" maxlength="150">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Sexo</label>
                    <select name="sexo" class="form-select form-select-sm @error('sexo') is-invalid @enderror">
                        <option value="">— No especificado —</option>
                        <option value="M" @selected(old('sexo') === 'M')>Masculino</option>
                        <option value="F" @selected(old('sexo') === 'F')>Femenino</option>
                        <option value="X" @selected(old('sexo') === 'X')>No binario</option>
                    </select>
                    @error('sexo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check mb-1">
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
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush
