@php $c = $cargo ?? null; @endphp

<div class="row g-3">
    {{-- Nombre --}}
    <div class="col-12">
        <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" value="{{ old('nombre', $c?->nombre) }}"
               class="form-control form-control-sm @error('nombre') is-invalid @enderror"
               required maxlength="255" autofocus>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Categoría --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Categoría</label>
        <select name="id_categoria" class="form-select form-select-sm @error('id_categoria') is-invalid @enderror">
            <option value="">— Sin categoría —</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" @selected(old('id_categoria', $c?->id_categoria) == $cat->id)>
                    {{ $cat->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text" style="font-size:.7rem">
            <a href="{{ route('categorias-cargo.index') }}" target="_blank">Gestionar categorías</a>
        </div>
    </div>

    {{-- Horas semanales --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Hs. semanales <span class="text-danger">*</span></label>
        <input type="number" name="horas_semanales" step="0.25" min="0" max="168"
               value="{{ old('horas_semanales', $c?->horas_semanales) }}"
               class="form-control form-control-sm @error('horas_semanales') is-invalid @enderror" required>
        @error('horas_semanales')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Índice --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Índice (por hora reloj)</label>
        <input type="number" name="indice" step="0.0001" min="0" max="99"
               value="{{ old('indice', $c?->indice) }}"
               class="form-control form-control-sm @error('indice') is-invalid @enderror"
               placeholder="Ej: 0.6667">
        @error('indice')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Activo --}}
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo"
                   @checked(old('activo', isset($c) ? $c->activo : true))>
            <label class="form-check-label small" for="activo">Activo</label>
        </div>
    </div>
</div>
