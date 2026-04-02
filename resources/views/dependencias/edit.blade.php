@extends('layouts.app')

@section('title', 'Editar Dependencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dependencias.index') }}">Dependencias</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dependencias.show', $dependencia) }}">{{ $dependencia->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-pencil me-1"></i> Editar Dependencia
    </h5>
    <a href="{{ route('dependencias.show', $dependencia) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:680px">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-diagram-3 me-1"></i> Datos de la dependencia
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('dependencias.update', $dependencia) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre', $dependencia->nombre) }}" required maxlength="200">
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Sigla</label>
                <input type="text" name="sigla" class="form-control form-control-sm @error('sigla') is-invalid @enderror"
                       value="{{ old('sigla', $dependencia->sigla) }}" maxlength="20">
                @error('sigla')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="form-control form-control-sm @error('descripcion') is-invalid @enderror">{{ old('descripcion', $dependencia->descripcion) }}</textarea>
                @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
                <select name="id_institucion" class="form-select form-select-sm @error('id_institucion') is-invalid @enderror" required>
                    <option value="">— Seleccione una institución —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(old('id_institucion', $dependencia->id_institucion) == $inst->id)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Dependencia padre</label>
                <select name="id_dependencia_padre" class="form-select form-select-sm @error('id_dependencia_padre') is-invalid @enderror">
                    <option value="">— Sin padre (raíz) —</option>
                    @foreach($padres as $padre)
                        @if($padre->id !== $dependencia->id)
                            <option value="{{ $padre->id }}" @selected(old('id_dependencia_padre', $dependencia->id_dependencia_padre) == $padre->id)>
                                {{ $padre->nombre }}
                                @if($padre->sigla) ({{ $padre->sigla }}) @endif
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('id_dependencia_padre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="hidden" name="activa" value="0">
                    <input class="form-check-input" type="checkbox" name="activa" id="activa" value="1"
                           @checked(old('activa', $dependencia->activa))>
                    <label class="form-check-label small" for="activa">Dependencia activa</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Actualizar
                </button>
                <a href="{{ route('dependencias.show', $dependencia) }}" class="btn btn-sm btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
