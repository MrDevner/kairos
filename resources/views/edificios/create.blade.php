@extends('layouts.app')

@section('title', 'Nuevo Edificio')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('edificios.index') }}">Edificios / Complejos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-plus-circle me-1"></i> Nuevo Edificio
    </h5>
    <a href="{{ route('edificios.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:680px">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-building me-1"></i> Datos del edificio
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('edificios.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre') }}" required maxlength="255" placeholder="Ej: Edificio Central, Complejo Deportivo" autofocus>
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
                          placeholder="Descripción opcional…">{{ old('descripcion') }}</textarea>
                @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
                <select name="id_institucion" class="form-select form-select-sm @error('id_institucion') is-invalid @enderror" required>
                    <option value="">— Seleccione una institución —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(old('id_institucion') == $inst->id)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="hidden" name="activo" value="0">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                           @checked(old('activo', 1))>
                    <label class="form-check-label small" for="activo">Edificio activo</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Guardar
                </button>
                <a href="{{ route('edificios.index') }}" class="btn btn-sm btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
