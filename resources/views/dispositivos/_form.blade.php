@php $m = $model ?? null; @endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre"
               class="form-control form-control-sm @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $m?->nombre) }}" required maxlength="255" autofocus>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold small">Estado</label>
        <div class="form-check mt-2">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1"
                   class="form-check-input" id="activo"
                   @checked(old('activo', $m?->activo ?? true))>
            <label class="form-check-label small" for="activo">Dispositivo activo</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
        <select name="id_institucion"
                class="form-select form-select-sm @error('id_institucion') is-invalid @enderror" required>
            <option value="">— Seleccione una institución —</option>
            @foreach($instituciones as $inst)
                <option value="{{ $inst->id }}"
                    @selected(old('id_institucion', $m?->id_institucion) == $inst->id)>
                    {{ $inst->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold small">Ubicación <span class="text-danger">*</span></label>
        <input type="text" name="ubicacion"
               class="form-control form-control-sm @error('ubicacion') is-invalid @enderror"
               value="{{ old('ubicacion', $m?->ubicacion) }}" required maxlength="255"
               placeholder="Ej: Planta baja — Recepción">
        @error('ubicacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
        <select name="tipo"
                class="form-select form-select-sm @error('tipo') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            <option value="biometrico" @selected(old('tipo', $m?->tipo) === 'biometrico')>Biométrico</option>
            <option value="web"        @selected(old('tipo', $m?->tipo) === 'web')>Web</option>
            <option value="otro"       @selected(old('tipo', $m?->tipo) === 'otro')>Otro</option>
        </select>
        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold small">Modo de conexión <span class="text-danger">*</span></label>
        <select name="modo_conexion"
                class="form-select form-select-sm @error('modo_conexion') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            <option value="directo_bd"  @selected(old('modo_conexion', $m?->modo_conexion) === 'directo_bd')>Directo a BD</option>
            <option value="importacion" @selected(old('modo_conexion', $m?->modo_conexion) === 'importacion')>Importación</option>
            <option value="web"         @selected(old('modo_conexion', $m?->modo_conexion) === 'web')>Web</option>
        </select>
        @error('modo_conexion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold small">Dirección IP</label>
        <input type="text" name="ip_address"
               class="form-control form-control-sm font-monospace @error('ip_address') is-invalid @enderror"
               value="{{ old('ip_address', $m?->ip_address) }}"
               placeholder="192.168.1.100">
        @error('ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold small">Configuración del terminal web</label>
        @php
            $cfgOld = old('configuracion');
            $checkedSolicitarPass = $cfgOld !== null
                ? ($cfgOld['solicitar_contrasena'] ?? '0') === '1'
                : (bool)(($m?->configuracion ?? [])['solicitar_contrasena'] ?? true);
        @endphp
        <div class="card border-0 bg-light rounded-2 p-3">
            <div class="form-check">
                <input type="hidden" name="configuracion[solicitar_contrasena]" value="0">
                <input type="checkbox" name="configuracion[solicitar_contrasena]" value="1"
                       class="form-check-input" id="cfg_solicitar_contrasena"
                       @checked($checkedSolicitarPass)>
                <label class="form-check-label small" for="cfg_solicitar_contrasena">
                    Solicitar contraseña al marcar
                </label>
                <div class="form-text text-muted">
                    Si está activo, el reloj web pedirá la contraseña del usuario antes de registrar la marca. Activado por defecto.
                </div>
            </div>
        </div>
    </div>
</div>
