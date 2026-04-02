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
    <div class="step active" id="ind-1">1. Datos generales</div>
    <div class="step" id="ind-2">2. Horario semanal</div>
    <div class="step" id="ind-3">3. Resumen y envío</div>
</div>

<form method="POST" action="{{ route('ddjj.store') }}" id="form-ddjj">
    @csrf

    {{-- ═══════════ PASO 1 ═══════════ --}}
    <div class="wizard-step active" id="step-1">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-1-circle me-1"></i> Paso 1: Datos generales
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Designación <span class="text-danger">*</span></label>
                        <select name="id_designacion" id="id_designacion" class="form-select form-select-sm @error('id_designacion') is-invalid @enderror" required>
                            <option value="">— Seleccione una designación —</option>
                            @foreach($designaciones as $des)
                                <option value="{{ $des->id }}" @selected(old('id_designacion') == $des->id)>
                                    {{ $des->usuario->nombre_completo ?? '?' }} — {{ $des->cargo->nombre ?? '?' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_designacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Fecha inicio <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_inicio" id="s1_fecha_inicio"
                               class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                               value="{{ old('fecha_inicio') }}" required>
                        @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Fecha fin <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_fin" id="s1_fecha_fin"
                               class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror"
                               value="{{ old('fecha_fin') }}" required>
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
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-2-circle me-1"></i> Paso 2: Horario semanal
            </div>
            <div class="card-body">
                @php
                    $dias = ['lunes','martes','miercoles','jueves','viernes'];
                    $diasLabels = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
                    $modalidades = ['presencial','remoto','mixto'];
                @endphp

                @foreach($dias as $i => $dia)
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="small">{{ $diasLabels[$i] }}</strong>
                        <button type="button" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2"
                                onclick="agregarHorario('{{ $dia }}')">
                            <i class="bi bi-plus-lg me-1"></i> Agregar horario
                        </button>
                    </div>
                    <div id="horarios-{{ $dia }}">
                        {{-- Filas de horario generadas dinámicamente --}}
                    </div>
                </div>
                @endforeach
            </div>
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
                            <strong>Designación:</strong>
                            <span id="res-designacion" class="ms-1">—</span>
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
                    Al guardar, la declaración quedará en estado <strong>Borrador</strong>. Luego podrá presentarla.
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
    const dias = ['lunes','martes','miercoles','jueves','viernes'];
    const diasLabels = { lunes:'Lunes', martes:'Martes', miercoles:'Miércoles', jueves:'Jueves', viernes:'Viernes' };
    let contadores = { lunes:0, martes:0, miercoles:0, jueves:0, viernes:0 };

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

    function agregarHorario(dia) {
        const idx = contadores[dia]++;
        const container = document.getElementById('horarios-' + dia);
        const div = document.createElement('div');
        div.className = 'horario-row d-flex flex-wrap gap-2 align-items-center';
        div.innerHTML = `
            <div>
                <label class="form-label form-label-sm mb-0 small">Entrada</label>
                <input type="time" name="horarios[${dia}][${idx}][hora_entrada]" class="form-control form-control-sm" style="width:120px">
            </div>
            <div>
                <label class="form-label form-label-sm mb-0 small">Salida</label>
                <input type="time" name="horarios[${dia}][${idx}][hora_salida]" class="form-control form-control-sm" style="width:120px">
            </div>
            <div>
                <label class="form-label form-label-sm mb-0 small">Modalidad</label>
                <select name="horarios[${dia}][${idx}][modalidad]" class="form-select form-select-sm" style="width:140px">
                    <option value="">—</option>
                    <option value="presencial">Presencial</option>
                    <option value="remoto">Remoto</option>
                    <option value="mixto">Mixto</option>
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

    function buildSummary() {
        const des = document.getElementById('id_designacion');
        document.getElementById('res-designacion').textContent = des.options[des.selectedIndex]?.text ?? '—';
        document.getElementById('res-fecha-ini').textContent = document.getElementById('s1_fecha_inicio').value || '—';
        document.getElementById('res-fecha-fin').textContent = document.getElementById('s1_fecha_fin').value || '—';

        let html = '<div class="table-responsive"><table class="table table-sm table-bordered small mb-0"><thead class="table-light"><tr><th>Día</th><th>Entrada</th><th>Salida</th><th>Modalidad</th></tr></thead><tbody>';
        let hasRows = false;
        dias.forEach(function(dia) {
            const rows = document.querySelectorAll('#horarios-' + dia + ' .horario-row');
            rows.forEach(function(row) {
                hasRows = true;
                const entrada = row.querySelector('[name*="hora_entrada"]')?.value || '—';
                const salida  = row.querySelector('[name*="hora_salida"]')?.value || '—';
                const modal   = row.querySelector('[name*="modalidad"]')?.value || '—';
                html += `<tr><td>${diasLabels[dia]}</td><td>${entrada}</td><td>${salida}</td><td>${modal}</td></tr>`;
            });
        });
        if (!hasRows) html += '<tr><td colspan="4" class="text-center text-muted">Sin horarios cargados</td></tr>';
        html += '</tbody></table></div>';
        document.getElementById('res-horarios').innerHTML = html;
    }
</script>
@endpush
