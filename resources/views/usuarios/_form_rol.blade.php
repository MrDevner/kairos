{{-- Rol --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Rol <span class="text-danger">*</span></label>
    <select name="id_rol_institucion" class="form-select form-select-sm" required>
        <option value="">— Seleccione un rol —</option>
        @foreach($rolesInst as $r)
            <option value="{{ $r->id }}">{{ $r->nombre }}</option>
        @endforeach
    </select>
</div>

{{-- Institución (solo Admin General elige; los demás tienen la activa fija) --}}
@if($esAdminGeneral)
    <div class="mb-3">
        <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
        <select name="id_institucion" class="form-select form-select-sm" required>
            <option value="">— Seleccione —</option>
            @foreach($listaInstituciones as $item)
                <option value="{{ $item['institucion']->id }}"
                        style="padding-left:{{ $item['nivel'] * 12 }}px">
                    {{ str_repeat('— ', $item['nivel']) }}{{ $item['institucion']->nombre }}
                </option>
            @endforeach
        </select>
    </div>
@else
    <div class="mb-3">
        <label class="form-label fw-semibold small">Institución</label>
        <input type="text" class="form-control form-control-sm" value="{{ $instActiva?->nombre ?? '—' }}" disabled>
        <input type="hidden" name="id_institucion" value="{{ $instActiva?->id }}">
    </div>
@endif

{{-- Fechas --}}
<div class="row g-2 mb-3">
    <div class="col-6">
        <label class="form-label fw-semibold small">Desde <span class="text-danger">*</span></label>
        <input type="date" name="fecha_desde" class="form-control form-control-sm"
               value="{{ now()->toDateString() }}" required>
    </div>
    <div class="col-6">
        <label class="form-label fw-semibold small">Hasta</label>
        <input type="date" name="fecha_hasta" class="form-control form-control-sm">
        <div class="form-text" style="font-size:.7rem">Vacío = sin vencimiento</div>
    </div>
</div>

{{-- Estado --}}
<div class="form-check">
    <input type="hidden" name="activo" value="0">
    <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo-rol" checked>
    <label class="form-check-label small" for="activo-rol">Asignación activa</label>
</div>
