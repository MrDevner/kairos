@php
    $m       = $evento ?? null;
    $tipo    = old('tipo', $m?->tipo ?? '');
    $alcance = old('alcance', $m ? ($m->esGeneral() ? 'general' : 'institucional') : 'institucional');
@endphp

<div class="row g-3">

    {{-- Alcance --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Alcance <span class="text-danger">*</span></label>
        <select name="alcance" id="alcance"
                class="form-select form-select-sm @error('alcance') is-invalid @enderror" required>
            <option value="institucional" @selected($alcance === 'institucional')>Institucional</option>
            <option value="general"       @selected($alcance === 'general')>General (todos)</option>
        </select>
        @error('alcance')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text" style="font-size:.7rem">
            <span id="alcance-hint-inst">Visible para la institución y sus dependientes</span>
            <span id="alcance-hint-gen" style="display:none">Visible para todas las instituciones (ej: feriado nacional)</span>
        </div>
    </div>

    {{-- Institución (solo si alcance = institucional) --}}
    <div class="col-md-5" id="bloque-institucion">
        <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
        <select name="id_institucion" id="id_institucion"
                class="form-select form-select-sm @error('id_institucion') is-invalid @enderror">
            <option value="">— Seleccione —</option>
            @foreach($instituciones as $inst)
                <option value="{{ $inst->id }}" @selected(old('id_institucion', $m?->id_institucion) == $inst->id)>
                    {{ $inst->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Fecha inicio --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Fecha inicio <span class="text-danger">*</span></label>
        <input type="date" name="fecha_inicio" id="fecha_inicio"
               class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
               value="{{ old('fecha_inicio', $m?->fecha_inicio?->toDateString() ?? ($fechaDefault ?? '')) }}" required>
        @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Fecha fin --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Fecha fin <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="date" name="fecha_fin" id="fecha_fin"
               class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror"
               value="{{ old('fecha_fin', $m?->fecha_fin?->toDateString()) }}">
        @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text" style="font-size:.7rem">Solo si el evento abarca más de un día</div>
    </div>

    {{-- Título --}}
    <div class="col-12">
        <label class="form-label fw-semibold small">Título <span class="text-danger">*</span></label>
        <input type="text" name="titulo"
               class="form-control form-control-sm @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $m?->titulo) }}" required maxlength="255" autofocus>
        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Descripción --}}
    <div class="col-12">
        <label class="form-label fw-semibold small">Descripción</label>
        <textarea name="descripcion" rows="2"
                  class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
                  placeholder="Detalle adicional del evento…">{{ old('descripcion', $m?->descripcion) }}</textarea>
        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Tipo --}}
    <div class="col-md-5">
        <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
        <select name="tipo" id="tipo"
                class="form-select form-select-sm @error('tipo') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            <option value="feriado"             @selected($tipo === 'feriado')>Feriado</option>
            <option value="dia_no_laborable"    @selected($tipo === 'dia_no_laborable')>Día no laborable</option>
            <option value="suspension_total"    @selected($tipo === 'suspension_total')>Suspensión total</option>
            <option value="suspension_parcial"  @selected($tipo === 'suspension_parcial')>Suspensión parcial</option>
            <option value="evento_condicional"  @selected($tipo === 'evento_condicional')>Evento condicional</option>
            <option value="paro"                @selected($tipo === 'paro')>Paro del personal</option>
        </select>
        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Afecta cómputo --}}
    <div class="col-md-3 d-flex align-items-end pb-1">
        <div class="form-check">
            <input type="hidden" name="afecta_computo" value="0">
            <input type="checkbox" name="afecta_computo" value="1"
                   class="form-check-input" id="afecta_computo"
                   @checked(old('afecta_computo', $m?->afecta_computo ?? true))>
            <label class="form-check-label small" for="afecta_computo">Afecta cómputo de asistencia</label>
        </div>
    </div>

    {{-- Horas (suspensión parcial / condicional) --}}
    <div class="col-12" id="bloque-horas" style="display:none">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Hora desde</label>
                <input type="time" name="hora_desde"
                       class="form-control form-control-sm @error('hora_desde') is-invalid @enderror"
                       value="{{ old('hora_desde', $m?->hora_desde) }}">
                @error('hora_desde')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Hora hasta</label>
                <input type="time" name="hora_hasta"
                       class="form-control form-control-sm @error('hora_hasta') is-invalid @enderror"
                       value="{{ old('hora_hasta', $m?->hora_hasta) }}">
                @error('hora_hasta')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

</div>

{{-- Condiciones (evento_condicional) --}}
<div id="bloque-condiciones" style="display:none">
    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-semibold mb-0" style="color:var(--azul)">
            <i class="bi bi-funnel me-1"></i> Condiciones del evento
        </h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-agregar-condicion">
            <i class="bi bi-plus-lg me-1"></i> Agregar condición
        </button>
    </div>
    <div id="contenedor-condiciones">
        @if($m && $m->tipo === 'evento_condicional' && $m->condiciones->isNotEmpty() && !session()->hasOldInput())
            @foreach($m->condiciones as $i => $cond)
                @include('calendario._fila_condicion', ['i' => $i, 'cond' => $cond, 'esParo' => false])
            @endforeach
        @elseif(old('tipo') === 'evento_condicional' && old('condiciones'))
            @foreach(old('condiciones') as $i => $cond)
                @include('calendario._fila_condicion', ['i' => $i, 'cond' => $cond, 'esParo' => false])
            @endforeach
        @endif
    </div>
    <p class="text-muted small mt-2 mb-0" id="msg-sin-condiciones"
       style="{{ ($m && $m->tipo === 'evento_condicional' && $m->condiciones->isNotEmpty()) || (old('tipo') === 'evento_condicional' && old('condiciones')) ? 'display:none' : '' }}">
        Sin condiciones. Permiten definir efectos distintos según características del empleado.
    </p>
</div>

{{-- Filtros de paro --}}
<div id="bloque-filtros-paro" style="display:none">
    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h6 class="fw-semibold mb-0" style="color:var(--azul)">
                <i class="bi bi-person-fill-slash me-1"></i> Filtros del paro
            </h6>
            <p class="text-muted small mb-0">
                Definí a qué empleados aplica el paro. Sin filtros → aplica a <strong>todos</strong>.
                Si el empleado no marca ese día, se computa como <em>ausencia justificada por paro</em>.
            </p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-warning" id="btn-agregar-filtro">
            <i class="bi bi-plus-lg me-1"></i> Agregar filtro
        </button>
    </div>
    <div id="contenedor-filtros">
        @if($m && $m->tipo === 'paro' && $m->condiciones->isNotEmpty() && !session()->hasOldInput())
            @foreach($m->condiciones as $i => $cond)
                @include('calendario._fila_condicion', ['i' => $i, 'cond' => $cond, 'esParo' => true])
            @endforeach
        @elseif(old('tipo') === 'paro' && old('condiciones'))
            @foreach(old('condiciones') as $i => $cond)
                @include('calendario._fila_condicion', ['i' => $i, 'cond' => $cond, 'esParo' => true])
            @endforeach
        @endif
    </div>
    <p class="text-muted small mt-2 mb-0" id="msg-sin-filtros"
       style="{{ ($m && $m->tipo === 'paro' && $m->condiciones->isNotEmpty()) || (old('tipo') === 'paro' && old('condiciones')) ? 'display:none' : '' }}">
        Sin filtros: el paro aplica a <strong>todo el personal</strong> de la institución.
    </p>
</div>

{{-- Template para nueva condición (evento_condicional) --}}
<template id="tpl-condicion">
    @include('calendario._fila_condicion', ['i' => '__IDX__', 'cond' => null, 'esParo' => false])
</template>

{{-- Template para nuevo filtro (paro) --}}
<template id="tpl-filtro">
    @include('calendario._fila_condicion', ['i' => '__IDX__', 'cond' => null, 'esParo' => true])
</template>

@push('scripts')
<script>
(function () {
    // ── Alcance ────────────────────────────────────────────────────────────
    const alcanceSelect   = document.getElementById('alcance');
    const bloqueInst      = document.getElementById('bloque-institucion');
    const instSelect      = document.getElementById('id_institucion');
    const hintInst        = document.getElementById('alcance-hint-inst');
    const hintGen         = document.getElementById('alcance-hint-gen');

    function actualizarAlcance() {
        const esGeneral = alcanceSelect.value === 'general';
        bloqueInst.style.display = esGeneral ? 'none' : '';
        instSelect.required      = !esGeneral;
        hintInst.style.display   = esGeneral ? 'none' : '';
        hintGen.style.display    = esGeneral ? '' : 'none';
        if (esGeneral) instSelect.value = '';
    }

    alcanceSelect.addEventListener('change', actualizarAlcance);
    actualizarAlcance();

    // ── Tipo ───────────────────────────────────────────────────────────────
    const tipoSelect      = document.getElementById('tipo');
    const bloqueHoras     = document.getElementById('bloque-horas');
    const bloqueCond      = document.getElementById('bloque-condiciones');
    const bloqueParoFilt  = document.getElementById('bloque-filtros-paro');
    const contenedor      = document.getElementById('contenedor-condiciones');
    const contenedorFilt  = document.getElementById('contenedor-filtros');
    const msgSin          = document.getElementById('msg-sin-condiciones');
    const msgSinFilt      = document.getElementById('msg-sin-filtros');
    const btnAgregar      = document.getElementById('btn-agregar-condicion');
    const btnAgregarFilt  = document.getElementById('btn-agregar-filtro');
    const tpl             = document.getElementById('tpl-condicion');
    const tplFilt         = document.getElementById('tpl-filtro');

    const TIPOS_HORAS = ['suspension_parcial', 'evento_condicional'];
    const CARGO_DATA  = @json($cargos->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]));
    const DEP_DATA    = @json($dependencias->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre]));
    const CAT_DATA    = @json($categorias->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]));

    function actualizarVisibilidad() {
        const t = tipoSelect.value;
        bloqueHoras.style.display    = TIPOS_HORAS.includes(t) ? '' : 'none';
        bloqueCond.style.display     = t === 'evento_condicional' ? '' : 'none';
        bloqueParoFilt.style.display = t === 'paro' ? '' : 'none';
    }

    tipoSelect.addEventListener('change', actualizarVisibilidad);
    actualizarVisibilidad();

    // Construye el select de valor según tipo_condicion
    function buildValorInput(fila, tipoCondicion, valorActual) {
        const wrapper = fila.querySelector('.valor-condicion-wrapper');
        wrapper.innerHTML = '';
        let el;

        if (tipoCondicion === 'sexo') {
            el = document.createElement('select');
            el.className = 'form-select form-select-sm';
            [['M','Masculino'],['F','Femenino']].forEach(([v,l]) => {
                const opt = new Option(l, v, false, v === valorActual);
                el.appendChild(opt);
            });
        } else if (tipoCondicion === 'cargo') {
            el = document.createElement('select');
            el.className = 'form-select form-select-sm';
            el.innerHTML = '<option value="">— Cargo —</option>';
            CARGO_DATA.forEach(c => {
                const opt = new Option(c.nombre, c.id, false, String(c.id) === String(valorActual));
                el.appendChild(opt);
            });
        } else if (tipoCondicion === 'categoria_cargo') {
            el = document.createElement('select');
            el.className = 'form-select form-select-sm';
            el.innerHTML = '<option value="">— Categoría —</option>';
            CAT_DATA.forEach(c => {
                const opt = new Option(c.nombre, c.id, false, String(c.id) === String(valorActual));
                el.appendChild(opt);
            });
        } else if (tipoCondicion === 'dependencia') {
            el = document.createElement('select');
            el.className = 'form-select form-select-sm';
            el.innerHTML = '<option value="">— Dependencia —</option>';
            DEP_DATA.forEach(d => {
                const opt = new Option(d.nombre, d.id, false, String(d.id) === String(valorActual));
                el.appendChild(opt);
            });
        } else {
            el = document.createElement('input');
            el.type = 'text';
            el.className = 'form-control form-control-sm';
            el.placeholder = 'Valor personalizado…';
            el.value = valorActual ?? '';
        }

        const idx = fila.dataset.idx;
        el.name = `condiciones[${idx}][valor_condicion]`;
        el.required = true;
        wrapper.appendChild(el);
    }

    function bindFila(fila) {
        const tipoCondSel = fila.querySelector('.tipo-condicion-select');
        const efectoSel   = fila.querySelector('.efecto-select');
        const minutosWrap = fila.querySelector('.minutos-wrapper');
        const btnElim     = fila.querySelector('.btn-eliminar-condicion');

        function actualizarMinutos() {
            minutosWrap.style.display = efectoSel.value === 'exencion' ? 'none' : '';
        }

        tipoCondSel.addEventListener('change', function () {
            buildValorInput(fila, this.value, null);
        });

        efectoSel.addEventListener('change', actualizarMinutos);
        actualizarMinutos();

        btnElim.addEventListener('click', function () {
            fila.remove();
            msgSin.style.display = contenedor.children.length === 0 ? '' : 'none';
        });
    }

    // Bind condiciones ya renderizadas (edit mode con condiciones existentes)
    contenedor.querySelectorAll('.fila-condicion').forEach(fila => {
        const tipoCondSel = fila.querySelector('.tipo-condicion-select');
        const valorActual = fila.querySelector('.valor-condicion-wrapper [name]')?.value;
        buildValorInput(fila, tipoCondSel.value, valorActual);
        bindFila(fila);
    });

    let idx = contenedor.querySelectorAll('.fila-condicion').length;

    btnAgregar.addEventListener('click', function () {
        const html = tpl.innerHTML.replaceAll('__IDX__', idx);
        contenedor.insertAdjacentHTML('beforeend', html);
        const nuevaFila = contenedor.lastElementChild;
        buildValorInput(nuevaFila, 'sexo', null);
        bindFila(nuevaFila);
        msgSin.style.display = 'none';
        idx++;
    });

    // ── Filtros de paro (sin efecto/minutos) ─────────────────────────────
    // Bind filas ya renderizadas para paro (edición)
    contenedorFilt.querySelectorAll('.fila-condicion').forEach(fila => {
        const tipoCondSel = fila.querySelector('.tipo-condicion-select');
        const valorActual = fila.querySelector('.valor-condicion-wrapper [name]')?.value;
        buildValorInput(fila, tipoCondSel.value, valorActual);
        bindFila(fila);
    });

    let idxFilt = contenedorFilt.querySelectorAll('.fila-condicion').length;

    btnAgregarFilt.addEventListener('click', function () {
        const html = tplFilt.innerHTML.replaceAll('__IDX__', idxFilt);
        contenedorFilt.insertAdjacentHTML('beforeend', html);
        const nuevaFila = contenedorFilt.lastElementChild;
        buildValorInput(nuevaFila, 'sexo', null);
        bindFila(nuevaFila);
        msgSinFilt.style.display = 'none';
        idxFilt++;
    });
})();
</script>
@endpush
