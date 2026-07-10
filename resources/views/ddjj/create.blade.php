@extends('layouts.app')

@section('title', 'Nueva DDJJ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ddjj.index') }}">DDJJ</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')
<style>
    .wizard-step { display: none; }
    .wizard-step.active { display: block; }
    .step-indicator { display: flex; gap: 0; margin-bottom: 1.5rem; }
    .step-indicator .step {
        flex: 1;
        padding: .5rem 1rem;
        background: #e9ecef;
        color: #6c757d;
        font-size: .82rem;
        font-weight: 600;
        text-align: center;
        border-right: 1px solid #fff;
        position: relative;
    }
    .step-indicator .step.active {
        background: var(--azul);
        color: #fff;
    }
    .step-indicator .step.done {
        background: var(--celeste);
        color: #fff;
    }
    .step-indicator .step:first-child { border-radius: .375rem 0 0 .375rem; }
    .step-indicator .step:last-child  { border-radius: 0 .375rem .375rem 0; border-right: none; }
    .horario-row { background: #f8f9fa; border-radius: .375rem; padding: .5rem; margin-bottom: .4rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-file-earmark-plus me-1"></i> Nueva Declaración Jurada
    </h5>
    <a href="{{ route('ddjj.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

{{-- Indicador de pasos --}}
<div class="step-indicator">
    <div class="step active" id="ind-1">1. Trabajador y datos generales</div>
    <div class="step" id="ind-2">2. Horario semanal</div>
    <div class="step" id="ind-3">3. Resumen y envío</div>
</div>

<form method="POST" action="{{ route('ddjj.store') }}" id="form-ddjj">
    @csrf

    {{-- ═══════════ PASO 1 ═══════════ --}}
    <div class="wizard-step active" id="step-1">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-1-circle me-1"></i> Paso 1: Trabajador y datos generales
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Trabajador <span class="text-danger">*</span></label>
                        @if($puedeGestionar)
                            <select name="id_usuario" id="sel-trabajador" class="form-select form-select-sm @error('id_usuario') is-invalid @enderror" style="width:100%" required>
                                <option value="">— Buscar trabajador —</option>
                            </select>
                        @else
                            <input type="hidden" name="id_usuario" value="{{ $user->id }}">
                            <input type="text" class="form-control form-control-sm" readonly value="{{ $user->nombre_completo }}">
                        @endif
                        @error('id_usuario')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Fecha inicio <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_inicio" id="s1_fecha_inicio"
                               class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                               value="{{ old('fecha_inicio', now()->toDateString()) }}" required>
                        @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Fecha fin</label>
                        <input type="date" name="fecha_fin" id="s1_fecha_fin"
                               class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror"
                               value="{{ old('fecha_fin') }}">
                        @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Observaciones</label>
                        <textarea name="observaciones" rows="3"
                                  class="form-control form-control-sm @error('observaciones') is-invalid @enderror"
                                  placeholder="Observaciones opcionales…">{{ old('observaciones') }}</textarea>
                        @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-sm" style="background:var(--azul);color:#fff" onclick="goStep(2)">
                Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    {{-- ═══════════ PASO 2 ═══════════ --}}
    <div class="wizard-step" id="step-2">
        <div id="bloques-designaciones">
            <p class="text-muted small">
                @if($puedeGestionar)
                    Seleccione un trabajador en el paso 1 para ver sus designaciones vigentes.
                @else
                    Cargando sus designaciones vigentes…
                @endif
            </p>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goStep(1)">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-sm" style="background:var(--azul);color:#fff" onclick="goStep(3)">
                Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    {{-- ═══════════ PASO 3 ═══════════ --}}
    <div class="wizard-step" id="step-3">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-3-circle me-1"></i> Paso 3: Resumen
            </div>
            <div class="card-body">
                <p class="small text-muted">Revise los datos antes de enviar la declaración jurada.</p>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-2 small">
                            <strong>Trabajador:</strong>
                            <span id="res-trabajador" class="ms-1">—</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 small">
                            <strong>Desde:</strong>
                            <span id="res-fecha-ini" class="ms-1">—</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 small">
                            <strong>Hasta:</strong>
                            <span id="res-fecha-fin" class="ms-1">—</span>
                        </div>
                    </div>
                </div>

                <div id="res-horarios" class="mb-3">
                    {{-- Llenado por JS --}}
                </div>

                <div class="alert alert-info small py-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Al guardar, cada designación con horario cargado quedará como una declaración jurada en estado <strong>Borrador</strong>. Luego podrán presentarse.
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="goStep(2)">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                <i class="bi bi-check-lg me-1"></i> Guardar DDJJ
            </button>
        </div>
    </div>

</form>
@endsection

@push('scripts')
<script>
    const DIAS = ['lunes','martes','miercoles','jueves','viernes'];
    const DIAS_LABEL = { lunes:'Lunes', martes:'Martes', miercoles:'Miércoles', jueves:'Jueves', viernes:'Viernes' };
    const EDIFICIOS = @json($edificios);
    let filaIdx = 0;

    @if($puedeGestionar)
    $('#sel-trabajador').select2({
        placeholder: 'Buscar trabajador por apellido, nombre o documento…',
        minimumInputLength: 3,
        language: {
            inputTooShort: function () { return 'Ingrese al menos 3 caracteres'; },
            searching:     function () { return 'Buscando…'; },
            noResults:     function () { return 'Sin resultados'; },
        },
        ajax: {
            url: '{{ route('ddjj.trabajadores-buscar') }}',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term }),
            processResults: data => data,
        },
    });
    $('#sel-trabajador').on('change', function () { cargarDesignaciones(this.value); });
    @else
    document.addEventListener('DOMContentLoaded', function () { cargarDesignaciones({{ $user->id }}); });
    @endif

    function cargarDesignaciones(idUsuario) {
        const cont = document.getElementById('bloques-designaciones');
        cont.innerHTML = '<p class="text-muted small">Cargando designaciones…</p>';

        if (!idUsuario) {
            cont.innerHTML = '<p class="text-muted small">Seleccione un trabajador en el paso 1 para ver sus designaciones vigentes.</p>';
            return;
        }

        fetch('{{ route('ddjj.designaciones-activas') }}?id_usuario=' + idUsuario)
            .then(r => r.json())
            .then(data => {
                cont.innerHTML = '';
                if (!data.length) {
                    cont.innerHTML = '<div class="alert alert-warning small mb-0">El trabajador no tiene designaciones vigentes en esta institución.</div>';
                    return;
                }
                data.forEach(d => cont.appendChild(renderBloqueDesignacion(d)));
            });
    }

    function renderBloqueDesignacion(d) {
        const wrapper = document.createElement('div');
        wrapper.className = 'card mb-3';
        wrapper.dataset.designacionText = d.text;

        let diasHtml = '';
        DIAS.forEach(dia => {
            diasHtml += `
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong class="small">${DIAS_LABEL[dia]}</strong>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                            onclick="agregarHorario(${d.id}, '${dia}')">
                        <i class="bi bi-plus-lg me-1"></i> Agregar horario
                    </button>
                </div>
                <div id="horarios-${d.id}-${dia}"></div>
            </div>`;
        });

        wrapper.innerHTML = `
            <div class="card-header" style="background:var(--celeste);color:#fff">
                <i class="bi bi-briefcase me-1"></i> ${d.text}
                ${d.horas_obligatorias ? `<span class="badge bg-light text-dark ms-2">${d.horas_obligatorias}h/sem</span>` : ''}
            </div>
            <div class="card-body">${diasHtml}</div>
        `;
        return wrapper;
    }

    function opcionesEdificios() {
        let html = '<option value="">— Sin especificar —</option>';
        EDIFICIOS.forEach(e => { html += `<option value="${e.id}">${e.nombre}</option>`; });
        return html;
    }

    function actualizarOficinas(selectEdificio) {
        const selectOficina = selectEdificio.closest('.horario-row').querySelector('[name*="[id_oficina]"]');
        const edificio = EDIFICIOS.find(e => String(e.id) === selectEdificio.value);
        let html = '<option value="">— Sin especificar —</option>';
        if (edificio) {
            edificio.oficinas.forEach(o => { html += `<option value="${o.id}">${o.nombre}</option>`; });
        }
        selectOficina.innerHTML = html;
    }

    function agregarHorario(idDesignacion, dia) {
        const idx = filaIdx++;
        const container = document.getElementById(`horarios-${idDesignacion}-${dia}`);
        const div = document.createElement('div');
        div.className = 'horario-row d-flex flex-wrap gap-2 align-items-center';
        div.innerHTML = `
            <input type="hidden" name="horarios[${idx}][id_designacion]" value="${idDesignacion}">
            <input type="hidden" name="horarios[${idx}][dia_semana]" value="${dia}">
            <div>
                <label class="form-label form-label-sm mb-0 small">Entrada</label>
                <input type="time" name="horarios[${idx}][hora_entrada]" class="form-control form-control-sm" style="width:120px">
            </div>
            <div>
                <label class="form-label form-label-sm mb-0 small">Salida</label>
                <input type="time" name="horarios[${idx}][hora_salida]" class="form-control form-control-sm" style="width:120px">
            </div>
            <div>
                <label class="form-label form-label-sm mb-0 small">Modalidad</label>
                <select name="horarios[${idx}][modalidad]" class="form-select form-select-sm" style="width:140px">
                    <option value="presencial" selected>Presencial</option>
                    <option value="remoto">Remoto</option>
                </select>
            </div>
            <div>
                <label class="form-label form-label-sm mb-0 small">Edificio</label>
                <select name="horarios[${idx}][id_edificio]" class="form-select form-select-sm" style="width:160px" onchange="actualizarOficinas(this)">
                    ${opcionesEdificios()}
                </select>
            </div>
            <div>
                <label class="form-label form-label-sm mb-0 small">Oficina/Aula</label>
                <select name="horarios[${idx}][id_oficina]" class="form-select form-select-sm" style="width:160px">
                    <option value="">— Sin especificar —</option>
                </select>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="this.closest('.horario-row').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    }

    function goStep(num) {
        document.querySelectorAll('.wizard-step').forEach(function(s) { s.classList.remove('active'); });
        document.getElementById('step-' + num).classList.add('active');

        document.querySelectorAll('.step-indicator .step').forEach(function(s, i) {
            s.classList.remove('active','done');
            if (i + 1 < num) s.classList.add('done');
            if (i + 1 === num) s.classList.add('active');
        });

        if (num === 3) buildSummary();
        window.scrollTo(0, 0);
    }

    function buildSummary() {
        @if($puedeGestionar)
            const selTrabajador = document.getElementById('sel-trabajador');
            document.getElementById('res-trabajador').textContent =
                (selTrabajador.selectedOptions[0] && selTrabajador.selectedOptions[0].textContent) || '—';
        @else
            document.getElementById('res-trabajador').textContent = '{{ $user->nombre_completo }}';
        @endif
        document.getElementById('res-fecha-ini').textContent = document.getElementById('s1_fecha_inicio').value || '—';
        document.getElementById('res-fecha-fin').textContent = document.getElementById('s1_fecha_fin').value || '—';

        let html = '<div class="table-responsive"><table class="table table-sm table-bordered small mb-0">'
            + '<thead class="table-light"><tr><th>Designación</th><th>Día</th><th>Entrada</th><th>Salida</th><th>Modalidad</th><th>Ubicación</th></tr></thead><tbody>';
        let hasRows = false;

        document.querySelectorAll('#bloques-designaciones > .card').forEach(function(bloque) {
            const designacionText = bloque.dataset.designacionText || '—';
            bloque.querySelectorAll('.horario-row').forEach(function(row) {
                hasRows = true;
                const dia     = row.querySelector('[name*="dia_semana"]')?.value || '—';
                const entrada = row.querySelector('[name*="hora_entrada"]')?.value || '—';
                const salida  = row.querySelector('[name*="hora_salida"]')?.value || '—';
                const modal   = row.querySelector('[name*="modalidad"]')?.value || '—';
                const selEdif = row.querySelector('[name*="[id_edificio]"]');
                const selOfi  = row.querySelector('[name*="[id_oficina]"]');
                const edifTxt = selEdif && selEdif.value ? selEdif.selectedOptions[0].textContent : '';
                const ofiTxt  = selOfi && selOfi.value ? selOfi.selectedOptions[0].textContent : '';
                const ubicacion = [edifTxt, ofiTxt].filter(Boolean).join(' — ') || '—';
                html += `<tr><td>${designacionText}</td><td>${DIAS_LABEL[dia] || dia}</td><td>${entrada}</td><td>${salida}</td><td>${modal}</td><td>${ubicacion}</td></tr>`;
            });
        });
        if (!hasRows) html += '<tr><td colspan="6" class="text-center text-muted">Sin horarios cargados</td></tr>';
        html += '</tbody></table></div>';
        document.getElementById('res-horarios').innerHTML = html;
    }
</script>
@endpush
