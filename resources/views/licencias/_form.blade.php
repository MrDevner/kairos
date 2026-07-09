@php $editando = isset($licencia); @endphp

@if(!$editando)
{{-- ── Crear: usuario + tipo + designación ── --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Usuario <span class="text-danger">*</span></label>
    @php
        $oldUsuarioId   = old('id_usuario');
        $oldUsuario     = $oldUsuarioId ? \App\Models\User::find($oldUsuarioId) : null;
    @endphp
    <select id="sel-usuario" name="id_usuario"
            class="@error('id_usuario') is-invalid @enderror" required>
        @if($oldUsuario)
            <option value="{{ $oldUsuario->id }}" selected>
                {{ $oldUsuario->apellidos }}, {{ $oldUsuario->nombres }}{{ $oldUsuario->documento ? ' ('.$oldUsuario->documento.')' : '' }}
            </option>
        @endif
    </select>
    @error('id_usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold small">Tipo de licencia <span class="text-danger">*</span></label>
    <select id="sel-tipo" name="id_tipo_licencia"
            class="form-select form-select-sm @error('id_tipo_licencia') is-invalid @enderror" required>
        <option value="">— Seleccione un tipo —</option>
        @foreach($tipos as $t)
            <option value="{{ $t->id }}"
                    data-doc="{{ $t->requiere_documentacion ? '1' : '0' }}"
                    data-afecta="{{ $t->afecta }}"
                    @selected(old('id_tipo_licencia') == $t->id)>
                {{ $t->nombre }}
                @if($t->requiere_documentacion) [requiere doc.] @endif
            </option>
        @endforeach
    </select>
    @error('id_tipo_licencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3" id="bloque-designacion">
    <label class="form-label fw-semibold small">Designación</label>
    <select id="sel-designacion" name="id_designacion"
            class="form-select form-select-sm @error('id_designacion') is-invalid @enderror">
        <option value="">— Seleccione primero un usuario —</option>
    </select>
    <div class="form-text" style="font-size:.7rem">Opcional. Requerida cuando el tipo afecta designación.</div>
    @error('id_designacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@else
{{-- ── Editar: campos de identificación en solo lectura ── --}}
<div class="mb-3">
    <label class="form-label fw-semibold small text-muted">Usuario</label>
    <input type="text" class="form-control form-control-sm bg-light"
           value="{{ $licencia->usuario->apellidos }}, {{ $licencia->usuario->nombres }}" disabled>
</div>
<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-semibold small text-muted">Tipo</label>
        <input type="text" class="form-control form-control-sm bg-light"
               value="{{ $licencia->tipoLicencia->nombre ?? '—' }}" disabled>
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-semibold small text-muted">Designación</label>
        <input type="text" class="form-control form-control-sm bg-light"
               value="{{ $licencia->designacion ? ($licencia->designacion->cargo->nombre ?? '—') : 'Sin designación' }}" disabled>
    </div>
</div>
@endif

{{-- ── Período ── --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-semibold small">Fecha inicio <span class="text-danger">*</span></label>
        <input type="date" name="fecha_inicio"
               value="{{ old('fecha_inicio', $editando ? $licencia->fecha_inicio?->toDateString() : '') }}"
               class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror" required>
        @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-semibold small">Fecha fin</label>
        <input type="date" name="fecha_fin"
               value="{{ old('fecha_fin', $editando ? $licencia->fecha_fin?->toDateString() : '') }}"
               class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror">
        <div class="form-text" style="font-size:.7rem">Vacío = sin fecha de fin</div>
        @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- ── Motivo ── --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Motivo</label>
    <textarea name="motivo" rows="3"
              class="form-control form-control-sm @error('motivo') is-invalid @enderror"
              maxlength="1000" placeholder="Descripción o motivo de la licencia…">{{ old('motivo', $editando ? $licencia->motivo : '') }}</textarea>
    @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@if(!$editando)
{{-- ── Documentación ── --}}
<div class="mb-3" id="bloque-documentacion" style="display:none">
    <label class="form-label fw-semibold small">
        Documentación <span class="text-danger" id="doc-requerida">*</span>
    </label>
    <input type="file" name="documentacion" id="inp-documentacion"
           class="form-control form-control-sm @error('documentacion') is-invalid @enderror"
           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
    <div class="form-text" style="font-size:.7rem">PDF, imágenes o Word. Máx. 5 MB.</div>
    @error('documentacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@php
$designacionesData = $designaciones->map(fn($d) => [
    'id'         => $d->id,
    'id_usuario' => $d->id_usuario,
    'label'      => ($d->cargo->nombre ?? '—') . ' — ' . ($d->institucion->nombre ?? '—'),
]);
@endphp

@push('scripts')
<script>
(function () {
    // ── Select2 usuario ───────────────────────────────────────────────────
    initSelect2Usuario('#sel-usuario');

    // ── Designaciones / documentación ────────────────────────────────────
    const designaciones = {!! json_encode($designacionesData) !!};

    const $selUsuario    = $('#sel-usuario');
    const selDesignacion = document.getElementById('sel-designacion');
    const selTipo        = document.getElementById('sel-tipo');
    const bloqueDoc      = document.getElementById('bloque-documentacion');
    const inpDoc         = document.getElementById('inp-documentacion');

    function actualizarDesignaciones() {
        const uid = parseInt($selUsuario.val());
        const filtradas = designaciones.filter(d => d.id_usuario === uid);
        selDesignacion.innerHTML = '<option value="">— Sin designación —</option>';
        filtradas.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.label;
            selDesignacion.appendChild(opt);
        });
    }

    function actualizarDocumentacion() {
        const opt = selTipo.options[selTipo.selectedIndex];
        const requiere = opt && opt.dataset.doc === '1';
        bloqueDoc.style.display = requiere ? '' : 'none';
        inpDoc.required = requiere;
    }

    // Select2 dispara 'change' nativo; escuchar con jQuery para compatibilidad
    $selUsuario.on('change', actualizarDesignaciones);
    selTipo.addEventListener('change', actualizarDocumentacion);

    if ($selUsuario.val()) actualizarDesignaciones();
    actualizarDocumentacion();
})();
</script>
@endpush
@endif
