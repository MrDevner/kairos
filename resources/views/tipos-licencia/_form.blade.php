{{-- Nombre --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
    <input type="text" name="nombre" value="{{ old('nombre', $tipo->nombre ?? '') }}"
           class="form-control form-control-sm @error('nombre') is-invalid @enderror"
           required maxlength="100" autofocus>
    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- Descripción --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Descripción</label>
    <textarea name="descripcion" rows="3"
              class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
              maxlength="1000">{{ old('descripcion', $tipo->descripcion ?? '') }}</textarea>
    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row g-3 mb-3">
    {{-- Cómputo --}}
    <div class="col-sm-6">
        <label class="form-label fw-semibold small">Cómputo <span class="text-danger">*</span></label>
        <select name="computo" class="form-select form-select-sm @error('computo') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            <option value="dias_corridos" @selected(old('computo', $tipo->computo ?? '') === 'dias_corridos')>Días corridos</option>
            <option value="dias_habiles"  @selected(old('computo', $tipo->computo ?? '') === 'dias_habiles')>Días hábiles</option>
        </select>
        @error('computo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Afecta --}}
    <div class="col-sm-6">
        <label class="form-label fw-semibold small">Afecta <span class="text-danger">*</span></label>
        <select name="afecta" class="form-select form-select-sm @error('afecta') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            <option value="usuario"      @selected(old('afecta', $tipo->afecta ?? '') === 'usuario')>Usuario</option>
            <option value="designacion"  @selected(old('afecta', $tipo->afecta ?? '') === 'designacion')>Designación</option>
        </select>
        @error('afecta') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Días máximos --}}
    <div class="col-sm-6">
        <label class="form-label fw-semibold small">Días máximos</label>
        <input type="number" name="dias_maximos" min="1"
               value="{{ old('dias_maximos', $tipo->dias_maximos ?? '') }}"
               class="form-control form-control-sm @error('dias_maximos') is-invalid @enderror">
        <div class="form-text" style="font-size:.7rem">Vacío = sin límite</div>
        @error('dias_maximos') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Institución --}}
    <div class="col-sm-6">
        <label class="form-label fw-semibold small">Institución</label>
        <select name="id_institucion" class="form-select form-select-sm @error('id_institucion') is-invalid @enderror">
            <option value="">— Global (todas las instituciones) —</option>
            @foreach($listaInstituciones as $item)
                <option value="{{ $item['institucion']->id }}"
                        @selected(old('id_institucion', $tipo->id_institucion ?? null) == $item['institucion']->id)>
                    {{ str_repeat('— ', $item['nivel']) }}{{ $item['institucion']->nombre }}
                </option>
            @endforeach
        </select>
        <div class="form-text" style="font-size:.7rem">
            Si se elige una institución, el tipo también será visible en sus instituciones hijas.
        </div>
        @error('id_institucion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Aplica a categoría de cargo --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Aplica a categoría de cargo</label>
    <select name="id_categoria_cargo"
            class="form-select form-select-sm @error('id_categoria_cargo') is-invalid @enderror">
        <option value="">— Todas las categorías —</option>
        @foreach($categorias as $cat)
            <option value="{{ $cat->id }}"
                    @selected(old('id_categoria_cargo', $tipo->id_categoria_cargo ?? null) == $cat->id)>
                {{ $cat->nombre }}
            </option>
        @endforeach
    </select>
    <div class="form-text" style="font-size:.7rem">
        Vacío = aplica a personal de todas las categorías (docente, no docente, etc.)
    </div>
    @error('id_categoria_cargo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- Requiere documentación --}}
<div class="form-check mb-2">
    <input type="hidden" name="requiere_documentacion" value="0">
    <input type="checkbox" name="requiere_documentacion" value="1" class="form-check-input"
           id="requiere-doc"
           @checked(old('requiere_documentacion', ($tipo->requiere_documentacion ?? false) ? '1' : '0') == '1')>
    <label class="form-check-label small" for="requiere-doc">Requiere documentación</label>
</div>

{{-- Activo --}}
<div class="form-check">
    <input type="hidden" name="activo" value="0">
    <input type="checkbox" name="activo" value="1" class="form-check-input"
           id="activo-tipo"
           @checked(old('activo', isset($tipo) ? ($tipo->activo ? '1' : '0') : '1') == '1')>
    <label class="form-check-label small" for="activo-tipo">Activo</label>
</div>
