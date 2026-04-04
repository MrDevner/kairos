{{--
  Partial reutilizable para selects de ubicación en cascada.
  Parámetros esperados:
    $paises          – Collection de Pais
    $prefijo         – string: prefijo único para IDs HTML (ej: 'dom', 'nac')
    $labelPais       – string: etiqueta del select de país
    $labelEstado     – string: etiqueta del select de estado
    $labelCiudad     – string: etiqueta del select de ciudad
    $idPaisActual    – int|null: valor preseleccionado de país
    $idEstadoActual  – int|null: valor preseleccionado de estado
    $idCiudadActual  – int|null: valor preseleccionado de ciudad
    $namePais        – string: name del campo país (ej: 'id_pais_nacimiento')
    $nameEstado      – string: name del campo estado
    $nameCiudad      – string: name del campo ciudad
    $colPais         – string: clase Bootstrap col (ej: 'col-md-4')
    $colEstado       – string: clase Bootstrap col
    $colCiudad       – string: clase Bootstrap col
    $readonly        – bool: si true, campos readonly
--}}

@php
    $readonly      = $readonly ?? false;
    $prefijo       = $prefijo ?? 'ub';
    $colPais       = $colPais   ?? 'col-md-4';
    $colEstado     = $colEstado ?? 'col-md-4';
    $colCiudad     = $colCiudad ?? 'col-md-4';
    $labelPais     = $labelPais   ?? 'País';
    $labelEstado   = $labelEstado ?? 'Estado/Provincia';
    $labelCiudad   = $labelCiudad ?? 'Ciudad/Departamento';
    $namePais      = $namePais    ?? 'id_pais';
    $nameEstado    = $nameEstado  ?? 'id_estado';
    $nameCiudad    = $nameCiudad  ?? 'id_ciudad';
    $idPaisActual  = old($namePais,   $idPaisActual  ?? null);
    $idEstadoActual= old($nameEstado, $idEstadoActual ?? null);
    $idCiudadActual= old($nameCiudad, $idCiudadActual ?? null);

    // Pre-cargar estados y ciudades para el valor actual
    $estadosActuales  = $idPaisActual  ? \App\Models\Estado::where('id_pais',   $idPaisActual)->orderBy('nombre')->get()  : collect();
    $ciudadesActuales = $idEstadoActual ? \App\Models\Ciudad::where('id_estado', $idEstadoActual)->orderBy('nombre')->get() : collect();
@endphp

<div class="{{ $colPais }}">
    <label class="form-label fw-semibold small">{{ $labelPais }}</label>
    <select name="{{ $namePais }}" id="sel_pais_{{ $prefijo }}"
            class="form-select form-select-sm @error($namePais) is-invalid @enderror"
            @if($readonly) disabled @endif>
        <option value="">— Seleccionar —</option>
        @foreach($paises as $p)
            <option value="{{ $p->id }}" @selected($idPaisActual == $p->id)>{{ $p->nombre }}</option>
        @endforeach
    </select>
    @error($namePais)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="{{ $colEstado }}">
    <label class="form-label fw-semibold small">{{ $labelEstado }}</label>
    <select name="{{ $nameEstado }}" id="sel_estado_{{ $prefijo }}"
            class="form-select form-select-sm @error($nameEstado) is-invalid @enderror"
            @if($readonly) disabled @endif>
        <option value="">— Seleccionar —</option>
        @foreach($estadosActuales as $e)
            <option value="{{ $e->id }}" @selected($idEstadoActual == $e->id)>{{ $e->nombre }}</option>
        @endforeach
    </select>
    @error($nameEstado)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="{{ $colCiudad }}">
    <label class="form-label fw-semibold small">{{ $labelCiudad }}</label>
    <select name="{{ $nameCiudad }}" id="sel_ciudad_{{ $prefijo }}"
            class="form-select form-select-sm @error($nameCiudad) is-invalid @enderror"
            @if($readonly) disabled @endif>
        <option value="">— Seleccionar —</option>
        @foreach($ciudadesActuales as $c)
            <option value="{{ $c->id }}" @selected($idCiudadActual == $c->id)>{{ $c->nombre }}</option>
        @endforeach
    </select>
    @error($nameCiudad)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@if(!$readonly)
@push('scripts')
<script>
(function () {
    const urlEstados  = '{{ route('ubicacion.estados') }}';
    const urlCiudades = '{{ route('ubicacion.ciudades') }}';
    const prefijo     = '{{ $prefijo }}';

    function poblarSelect(select, datos, valorActual) {
        select.innerHTML = '<option value="">— Seleccionar —</option>';
        datos.forEach(function (item) {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nombre;
            if (String(item.id) === String(valorActual)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    const selPais   = document.getElementById('sel_pais_'   + prefijo);
    const selEstado = document.getElementById('sel_estado_' + prefijo);
    const selCiudad = document.getElementById('sel_ciudad_' + prefijo);

    if (!selPais) return;

    selPais.addEventListener('change', function () {
        selEstado.innerHTML = '<option value="">— Seleccionar —</option>';
        selCiudad.innerHTML = '<option value="">— Seleccionar —</option>';
        if (!this.value) return;
        fetch(urlEstados + '?id_pais=' + this.value)
            .then(r => r.json())
            .then(data => poblarSelect(selEstado, data, null));
    });

    selEstado.addEventListener('change', function () {
        selCiudad.innerHTML = '<option value="">— Seleccionar —</option>';
        if (!this.value) return;
        fetch(urlCiudades + '?id_estado=' + this.value)
            .then(r => r.json())
            .then(data => poblarSelect(selCiudad, data, null));
    });
})();
</script>
@endpush
@endif
