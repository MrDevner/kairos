{{-- Formulario compartido create/edit de MarcaOriginal --}}
@php $m = $marca ?? null; @endphp

<div class="mb-3">
    <label class="form-label fw-semibold small">Empleado <span class="text-danger">*</span></label>
    <select name="id_usuario" class="form-select form-select-sm @error('id_usuario') is-invalid @enderror" required>
        <option value="">— Seleccione un empleado —</option>
        @foreach($usuarios as $u)
            <option value="{{ $u->id }}" @selected(old('id_usuario', $m?->id_usuario) == $u->id)>
                {{ $u->apellidos }}, {{ $u->nombres }}
                @if($u->documento) ({{ $u->documento }})@endif
            </option>
        @endforeach
    </select>
    @error('id_usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold small">Dispositivo <span class="text-danger">*</span></label>
    <select name="id_dispositivo" class="form-select form-select-sm @error('id_dispositivo') is-invalid @enderror" required>
        <option value="">— Seleccione un dispositivo —</option>
        @foreach($dispositivos as $d)
            <option value="{{ $d->id }}" @selected(old('id_dispositivo', $m?->id_dispositivo) == $d->id)>
                {{ $d->nombre }}
                @if($d->ubicacion) — {{ $d->ubicacion }}@endif
            </option>
        @endforeach
    </select>
    @error('id_dispositivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label class="form-label fw-semibold small">Fecha y hora <span class="text-danger">*</span></label>
    @php $fechaHoraVal = $m?->fecha_hora ? \Carbon\Carbon::parse($m->fecha_hora)->format('Y-m-d\TH:i') : ''; @endphp
    <input type="datetime-local" name="fecha_hora"
           value="{{ old('fecha_hora', $fechaHoraVal) }}"
           class="form-control form-control-sm @error('fecha_hora') is-invalid @enderror" required>
    @error('fecha_hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-check-lg me-1"></i> {{ isset($marca) ? 'Actualizar' : 'Registrar' }}
    </button>
    <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
</div>
