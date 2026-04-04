@extends('layouts.app')

@section('title', 'Registrar aviso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('avisos.index') }}">Avisos del personal</a></li>
    <li class="breadcrumb-item active">Registrar aviso</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-megaphone me-1"></i> Registrar aviso
    </h5>
    <a href="{{ route('avisos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:680px">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-megaphone me-1"></i> Datos del aviso
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('avisos.store') }}" id="form-aviso">
            @csrf

            {{-- Institución --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
                <select name="id_institucion" id="sel-institucion"
                        class="form-select form-select-sm @error('id_institucion') is-invalid @enderror" required>
                    <option value="">— Seleccionar institución —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}"
                            @selected(old('id_institucion', $instId) == $inst->id)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Empleado (Select2) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Empleado <span class="text-danger">*</span></label>
                <select name="id_usuario" id="sel-usuario"
                        class="form-select form-select-sm @error('id_usuario') is-invalid @enderror"
                        style="width:100%" required>
                    <option value="">— Buscar empleado —</option>
                </select>
                @error('id_usuario')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- Designación --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Designación / Dependencia <span class="text-danger">*</span></label>
                <select name="id_designacion" id="sel-designacion"
                        class="form-select form-select-sm @error('id_designacion') is-invalid @enderror" required>
                    <option value="">— Primero seleccione un empleado —</option>
                </select>
                @error('id_designacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Fechas --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">
                        Fecha del aviso <span class="text-danger">*</span>
                        <span class="text-muted fw-normal">(cuándo se notifica)</span>
                    </label>
                    <input type="date" name="fecha_aviso"
                           class="form-control form-control-sm @error('fecha_aviso') is-invalid @enderror"
                           value="{{ old('fecha_aviso', date('Y-m-d')) }}" required>
                    @error('fecha_aviso')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">
                        Fecha del evento <span class="text-danger">*</span>
                        <span class="text-muted fw-normal">(cuándo ocurre)</span>
                    </label>
                    <input type="date" name="fecha_evento"
                           class="form-control form-control-sm @error('fecha_evento') is-invalid @enderror"
                           value="{{ old('fecha_evento', date('Y-m-d')) }}" required>
                    @error('fecha_evento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Tipo --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small d-block">Tipo de aviso <span class="text-danger">*</span></label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="tipo" id="tipo-ausencia"
                           value="ausencia" @checked(old('tipo', 'ausencia') === 'ausencia') required>
                    <label class="btn btn-sm btn-outline-warning" for="tipo-ausencia">
                        <i class="bi bi-calendar-x me-1"></i> Ausencia
                    </label>

                    <input type="radio" class="btn-check" name="tipo" id="tipo-tardanza"
                           value="tardanza" @checked(old('tipo') === 'tardanza')>
                    <label class="btn btn-sm btn-outline-info" for="tipo-tardanza">
                        <i class="bi bi-clock me-1"></i> Tardanza
                    </label>
                </div>
                @error('tipo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Detalle ausencia: tipo licencia --}}
            <div id="bloque-ausencia" class="mb-3">
                <label class="form-label fw-semibold small">Motivo (tipo de licencia) <span class="text-danger">*</span></label>
                <select name="id_tipo_licencia" id="sel-tipo-licencia"
                        class="form-select form-select-sm @error('id_tipo_licencia') is-invalid @enderror">
                    <option value="">— Seleccionar motivo —</option>
                    @foreach($tiposLicencia as $tl)
                        <option value="{{ $tl->id }}" @selected(old('id_tipo_licencia') == $tl->id)>
                            {{ $tl->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_tipo_licencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Detalle tardanza: hora estimada --}}
            <div id="bloque-tardanza" class="mb-3 d-none">
                <label class="form-label fw-semibold small">Hora estimada de llegada <span class="text-danger">*</span></label>
                <input type="time" name="hora_estimada_llegada"
                       class="form-control form-control-sm @error('hora_estimada_llegada') is-invalid @enderror"
                       value="{{ old('hora_estimada_llegada') }}">
                @error('hora_estimada_llegada')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Observaciones --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Observaciones</label>
                <textarea name="motivo" rows="2"
                          class="form-control form-control-sm @error('motivo') is-invalid @enderror"
                          placeholder="Observaciones adicionales (opcional)…" maxlength="1000">{{ old('motivo') }}</textarea>
                @error('motivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Registrar aviso
                </button>
                <a href="{{ route('avisos.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
.select2-container--default .select2-selection--single {
    height: calc(1.5em + .5rem + 2px);
    border: 1px solid #dee2e6;
    border-radius: .25rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: calc(1.5em + .5rem);
    font-size: .875rem;
    padding-left: .5rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100%;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    // ── Select2 para búsqueda de empleado ──────────────────────────────────
    $('#sel-usuario').select2({
        placeholder: 'Buscar por apellido, nombre o documento…',
        minimumInputLength: 3,
        ajax: {
            url: '{{ route('usuarios.buscar') }}',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term }),
            processResults: data => data,
        },
    });

    // ── Cargar designaciones al cambiar empleado ───────────────────────────
    const selDesignacion = document.getElementById('sel-designacion');
    const selInstitucion = document.getElementById('sel-institucion');

    $('#sel-usuario').on('change', function () {
        const idUsuario = this.value;
        selDesignacion.innerHTML = '<option value="">— Cargando… —</option>';
        if (!idUsuario) {
            selDesignacion.innerHTML = '<option value="">— Primero seleccione un empleado —</option>';
            return;
        }
        fetch('{{ route('avisos.designaciones-usuario') }}?id_usuario=' + idUsuario)
            .then(r => r.json())
            .then(data => {
                selDesignacion.innerHTML = '<option value="">— Seleccionar designación —</option>';
                data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.text + ' (' + d.institucion + ')';
                    opt.dataset.idInstitucion = d.id_institucion;
                    selDesignacion.appendChild(opt);
                });
                // Si hay solo una, seleccionarla automáticamente
                if (data.length === 1) {
                    selDesignacion.value = data[0].id;
                    // Actualizar institución automáticamente
                    if (data[0].id_institucion && selInstitucion) {
                        selInstitucion.value = data[0].id_institucion;
                    }
                }
            });
    });

    // Al cambiar designación, sincronizar institución
    selDesignacion.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const idInst   = selected?.dataset?.idInstitucion;
        if (idInst && selInstitucion) {
            selInstitucion.value = idInst;
        }
    });

    // ── Toggle ausencia / tardanza ─────────────────────────────────────────
    const bloqueAusencia = document.getElementById('bloque-ausencia');
    const bloqueTardanza = document.getElementById('bloque-tardanza');
    const selTipoLicencia = document.getElementById('sel-tipo-licencia');
    const inputHora       = document.querySelector('input[name="hora_estimada_llegada"]');

    function toggleTipo() {
        const esTardanza = document.getElementById('tipo-tardanza').checked;
        bloqueAusencia.classList.toggle('d-none', esTardanza);
        bloqueTardanza.classList.toggle('d-none', !esTardanza);
        selTipoLicencia.required = !esTardanza;
        inputHora.required = esTardanza;
    }

    document.getElementById('tipo-ausencia').addEventListener('change', toggleTipo);
    document.getElementById('tipo-tardanza').addEventListener('change', toggleTipo);

    toggleTipo(); // estado inicial
})();
</script>
@endpush
