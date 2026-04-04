@extends('layouts.app')

@section('title', 'Editar Designación')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('designaciones.index') }}">Designaciones</a></li>
    <li class="breadcrumb-item"><a href="{{ route('designaciones.show', $designacion) }}">{{ $designacion->usuario->nombre_completo ?? 'Designación' }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-pencil me-1"></i> Editar Designación
    </h5>
    <a href="{{ route('designaciones.show', $designacion) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:760px">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-briefcase me-1"></i> Datos de la designación
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('designaciones.update', $designacion) }}" id="form-designacion">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Usuario <span class="text-danger">*</span></label>
                    @php
                        $preUid  = old('id_usuario', $designacion->id_usuario);
                        $preUser = $preUid ? \App\Models\Usuario::find($preUid) : null;
                    @endphp
                    <select id="sel-usuario-desig" name="id_usuario"
                            class="@error('id_usuario') is-invalid @enderror" required>
                        @if($preUser)
                            <option value="{{ $preUser->id }}" selected>
                                {{ $preUser->apellidos }}, {{ $preUser->nombres }}{{ $preUser->documento ? ' ('.$preUser->documento.')' : '' }}
                            </option>
                        @endif
                    </select>
                    @error('id_usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Cargo <span class="text-danger">*</span></label>
                    <select name="id_cargo" id="id_cargo" class="form-select form-select-sm @error('id_cargo') is-invalid @enderror" required>
                        <option value="">— Seleccione un cargo —</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}"
                                    data-horas="{{ $cargo->horas_semanales ?? '' }}"
                                    @selected(old('id_cargo', $designacion->id_cargo) == $cargo->id)>
                                {{ $cargo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cargo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text small" id="horas-cargo-info"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
                    <select name="id_institucion" id="id_institucion" class="form-select form-select-sm @error('id_institucion') is-invalid @enderror" required>
                        <option value="">— Seleccione una institución —</option>
                        @foreach($instituciones as $inst)
                            <option value="{{ $inst->id }}" @selected(old('id_institucion', $designacion->id_institucion) == $inst->id)>
                                {{ $inst->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Dependencia</label>
                    <select name="id_dependencia" id="id_dependencia" class="form-select form-select-sm @error('id_dependencia') is-invalid @enderror">
                        <option value="">— Sin dependencia —</option>
                        @foreach($dependencias as $dep)
                            <option value="{{ $dep->id }}"
                                    data-institucion="{{ $dep->id_institucion }}"
                                    @selected(old('id_dependencia', $designacion->id_dependencia) == $dep->id)>
                                {{ $dep->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_dependencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Fecha inicio <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                           value="{{ old('fecha_inicio', $designacion->fecha_inicio) }}" required>
                    @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror"
                           value="{{ old('fecha_fin', $designacion->fecha_fin) }}">
                    @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Resolución</label>
                    <input type="text" name="resolucion" class="form-control form-control-sm @error('resolucion') is-invalid @enderror"
                           value="{{ old('resolucion', $designacion->resolucion) }}" maxlength="100">
                    @error('resolucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Horas semanales efectivas</label>
                    <input type="number" name="horas_semanales_efectivas" class="form-control form-control-sm @error('horas_semanales_efectivas') is-invalid @enderror"
                           value="{{ old('horas_semanales_efectivas', $designacion->horas_semanales_efectivas) }}" min="1" max="168" step="0.5">
                    <div class="form-text small">Dejar vacío para usar las horas del cargo.</div>
                    @error('horas_semanales_efectivas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activa" id="activa" value="1"
                               @checked(old('activa', $designacion->activa))>
                        <label class="form-check-label small" for="activa">Designación activa</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Actualizar
                </button>
                <a href="{{ route('designaciones.show', $designacion) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    initSelect2Usuario('#sel-usuario-desig');

    document.getElementById('id_cargo').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const horas = opt.dataset.horas;
        const info = document.getElementById('horas-cargo-info');
        info.textContent = horas ? 'Este cargo tiene ' + horas + ' horas semanales.' : '';
    });

    document.getElementById('id_institucion').addEventListener('change', function () {
        const instId = this.value;
        const select = document.getElementById('id_dependencia');
        const opts = select.querySelectorAll('option[data-institucion]');
        opts.forEach(function (opt) {
            opt.style.display = (!instId || opt.dataset.institucion === instId) ? '' : 'none';
        });
    });

    // Trigger on load
    document.getElementById('id_cargo').dispatchEvent(new Event('change'));
    document.getElementById('id_institucion').dispatchEvent(new Event('change'));
</script>
@endpush
