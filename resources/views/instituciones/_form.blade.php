@php $m = $model ?? null; @endphp

{{-- ── Datos básicos ──────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre"
               class="form-control form-control-sm @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $m?->nombre) }}" required maxlength="255" autofocus>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Sigla</label>
        <input type="text" name="sigla"
               class="form-control form-control-sm @error('sigla') is-invalid @enderror"
               value="{{ old('sigla', $m?->sigla) }}" maxlength="20"
               placeholder="Ej: UNSJ">
        @error('sigla')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold small">Descripción</label>
        <textarea name="descripcion"
                  class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
                  rows="2">{{ old('descripcion', $m?->descripcion) }}</textarea>
        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Institución padre</label>
        <select name="id_institucion_padre"
                class="form-select form-select-sm @error('id_institucion_padre') is-invalid @enderror">
            <option value="">— Ninguna (raíz) —</option>
            @foreach($padres as $p)
                <option value="{{ $p->id }}"
                    @selected(old('id_institucion_padre', $m?->id_institucion_padre) == $p->id)>
                    {{ $p->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_institucion_padre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-end pb-1">
        <div class="form-check">
            <input type="hidden" name="activa" value="0">
            <input type="checkbox" name="activa" value="1"
                   class="form-check-input" id="activa"
                   @checked(old('activa', $m?->activa ?? true))>
            <label class="form-check-label small fw-semibold" for="activa">Institución activa</label>
        </div>
    </div>
</div>

{{-- ── Contacto ────────────────────────────────────────────────── --}}
<hr class="my-2">
<p class="small fw-semibold text-muted mb-2">Información de contacto</p>
<div class="row g-3 mb-3">
    <div class="col-12">
        <label class="form-label fw-semibold small">Dirección</label>
        <input type="text" name="direccion"
               class="form-control form-control-sm @error('direccion') is-invalid @enderror"
               value="{{ old('direccion', $m?->direccion) }}" maxlength="255"
               placeholder="Av. José I. de la Roza...">
        @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label fw-semibold small">Teléfono</label>
        <input type="text" name="telefono"
               class="form-control form-control-sm @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $m?->telefono) }}" maxlength="30"
               placeholder="264 4-222000">
        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-7">
        <label class="form-label fw-semibold small">Email institucional</label>
        <input type="email" name="email"
               class="form-control form-control-sm @error('email') is-invalid @enderror"
               value="{{ old('email', $m?->email) }}" maxlength="150"
               placeholder="info@unsj.edu.ar">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Configuración ───────────────────────────────────────────── --}}
<hr class="my-2">
<p class="small fw-semibold text-muted mb-2">Configuración operativa</p>
@php $cfg = $m?->configuracion ?? []; @endphp
<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label fw-semibold small">Umbral mínimo de jornada</label>
        <div class="input-group input-group-sm">
            <input type="number" name="cfg_umbral_jornada_minima"
                   class="form-control form-control-sm"
                   value="{{ old('cfg_umbral_jornada_minima', $cfg['umbral_jornada_minima'] ?? 60) }}"
                   min="0" max="1440">
            <span class="input-group-text">min</span>
        </div>
        <div class="form-text small">Tiempo mínimo para registrar jornada.</div>
    </div>
    <div class="col-md-7">
        <label class="form-label fw-semibold small">Banco de horas por</label>
        <select name="cfg_banco_horas_por" class="form-select form-select-sm">
            <option value="usuario"     @selected(old('cfg_banco_horas_por', $cfg['banco_horas_por'] ?? 'usuario') === 'usuario')>Usuario</option>
            <option value="designacion" @selected(old('cfg_banco_horas_por', $cfg['banco_horas_por'] ?? 'usuario') === 'designacion')>Designación</option>
        </select>
    </div>
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check">
            <input type="hidden" name="cfg_permite_avisos_usuario" value="0">
            <input type="checkbox" name="cfg_permite_avisos_usuario" value="1"
                   class="form-check-input" id="cfg_avisos"
                   @checked(old('cfg_permite_avisos_usuario', ($cfg['permite_avisos_usuario'] ?? false) ? '1' : '0') == '1')>
            <label class="form-check-label small" for="cfg_avisos">Permite avisos de usuario</label>
        </div>
    </div>
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check">
            <input type="hidden" name="cfg_horas_extra_autorizadas" value="0">
            <input type="checkbox" name="cfg_horas_extra_autorizadas" value="1"
                   class="form-check-input" id="cfg_horas_extra"
                   @checked(old('cfg_horas_extra_autorizadas', ($cfg['horas_extra_autorizadas'] ?? false) ? '1' : '0') == '1')>
            <label class="form-check-label small" for="cfg_horas_extra">Horas extra autorizadas</label>
        </div>
    </div>
</div>
