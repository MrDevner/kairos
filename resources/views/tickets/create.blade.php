@extends('layouts.app')

@section('title', 'Nuevo ticket')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)"><i class="bi bi-plus-lg me-1"></i> Nuevo ticket</h5>
    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Título <span class="text-danger">*</span></label>
                    <input type="text" name="titulo" class="form-control form-control-sm @error('titulo') is-invalid @enderror"
                           value="{{ old('titulo') }}" required maxlength="200" autofocus>
                    @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Categoría <span class="text-danger">*</span></label>
                    <select name="categoria" class="form-select form-select-sm @error('categoria') is-invalid @enderror" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c }}" @selected(old('categoria') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                    @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Prioridad <span class="text-danger">*</span></label>
                    <select name="prioridad" class="form-select form-select-sm @error('prioridad') is-invalid @enderror" required>
                        @foreach(\App\Models\Ticket::PRIORIDADES as $p)
                            <option value="{{ $p }}" @selected(old('prioridad', 'media') === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                    @error('prioridad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if($esSoporte)
                <div class="col-12">
                    <label class="form-label fw-semibold small">Abrir en nombre de</label>
                    <select name="id_creador" class="form-select form-select-sm">
                        <option value="">— Yo mismo —</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" @selected((string) old('id_creador') === (string) $u->id)>{{ $u->apellidos }}, {{ $u->nombres }}</option>
                        @endforeach
                    </select>
                    <div class="form-text small">Soporte puede abrir el ticket en nombre de otro usuario.</div>
                </div>
                @endif

                <div class="col-12">
                    <label class="form-label fw-semibold small">Descripción <span class="text-danger">*</span></label>
                    <textarea name="descripcion" class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
                              rows="5" required maxlength="5000">{{ old('descripcion') }}</textarea>
                    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Adjuntos</label>
                    <input type="file" name="adjuntos[]" multiple class="form-control form-control-sm @error('adjuntos.*') is-invalid @enderror">
                    <div class="form-text small">Máximo 10MB por archivo.</div>
                    @error('adjuntos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Crear ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
