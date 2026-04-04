@extends('layouts.app')

@section('title', 'Editar aviso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('avisos.index') }}">Avisos del personal</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avisos.show', $aviso) }}">#{{ $aviso->id }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-pencil me-1"></i> Editar aviso
    </h5>
    <a href="{{ route('avisos.show', $aviso) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:680px">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-megaphone me-1"></i>
        Aviso de <strong>{{ $aviso->usuario?->nombre_completo }}</strong>
    </div>
    <div class="card-body">
        {{-- Datos de sólo lectura --}}
        <div class="row g-2 mb-3 p-2 rounded" style="background:#f8f9fa">
            <div class="col-md-6">
                <span class="form-label fw-semibold small d-block text-muted mb-0">Empleado</span>
                <span class="small">{{ $aviso->usuario?->nombre_completo ?? '—' }}</span>
            </div>
            <div class="col-md-6">
                <span class="form-label fw-semibold small d-block text-muted mb-0">Dependencia</span>
                <span class="small">{{ $aviso->designacion?->dependencia?->nombre ?? '—' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('avisos.update', $aviso) }}" id="form-aviso">
            @csrf @method('PUT')

            {{-- Fechas --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">
                        Fecha del aviso <span class="text-danger">*</span>
                        <span class="text-muted fw-normal">(cuándo se notificó)</span>
                    </label>
                    <input type="date" name="fecha_aviso"
                           class="form-control form-control-sm @error('fecha_aviso') is-invalid @enderror"
                           value="{{ old('fecha_aviso', $aviso->fecha_aviso?->format('Y-m-d')) }}" required>
                    @error('fecha_aviso')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">
                        Fecha del evento <span class="text-danger">*</span>
                        <span class="text-muted fw-normal">(cuándo ocurrirá/ocurrió)</span>
                    </label>
                    <input type="date" name="fecha_evento"
                           class="form-control form-control-sm @error('fecha_evento') is-invalid @enderror"
                           value="{{ old('fecha_evento', $aviso->fecha_evento?->format('Y-m-d')) }}" required>
                    @error('fecha_evento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Tipo --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small d-block">Tipo de aviso <span class="text-danger">*</span></label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="tipo" id="tipo-ausencia"
                           value="ausencia" @checked(old('tipo', $aviso->tipo) === 'ausencia') required>
                    <label class="btn btn-sm btn-outline-warning" for="tipo-ausencia">
                        <i class="bi bi-calendar-x me-1"></i> Ausencia
                    </label>

                    <input type="radio" class="btn-check" name="tipo" id="tipo-tardanza"
                           value="tardanza" @checked(old('tipo', $aviso->tipo) === 'tardanza')>
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
                        <option value="{{ $tl->id }}"
                            @selected(old('id_tipo_licencia', $aviso->id_tipo_licencia) == $tl->id)>
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
                       value="{{ old('hora_estimada_llegada', $aviso->hora_estimada_llegada) }}">
                @error('hora_estimada_llegada')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Observaciones --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Observaciones</label>
                <textarea name="motivo" rows="2"
                          class="form-control form-control-sm @error('motivo') is-invalid @enderror"
                          placeholder="Observaciones adicionales (opcional)…" maxlength="1000">{{ old('motivo', $aviso->motivo) }}</textarea>
                @error('motivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Guardar cambios
                </button>
                <a href="{{ route('avisos.show', $aviso) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const bloqueAusencia  = document.getElementById('bloque-ausencia');
    const bloqueTardanza  = document.getElementById('bloque-tardanza');
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

    toggleTipo();
})();
</script>
@endpush
