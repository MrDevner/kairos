@extends('layouts.app')

@section('title', 'Importar Marcas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas</a></li>
    <li class="breadcrumb-item active">Importar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-upload me-1"></i> Importar Marcas
    </h5>
    <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Cargar archivo de marcas
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('marcas.importar.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Dispositivo <span class="text-danger">*</span></label>
                        <select name="id_dispositivo" class="form-select form-select-sm @error('id_dispositivo') is-invalid @enderror" required>
                            <option value="">— Seleccione un dispositivo —</option>
                            @foreach($dispositivos as $disp)
                                <option value="{{ $disp->id }}" @selected(old('id_dispositivo') == $disp->id)>
                                    {{ $disp->nombre }}
                                    @if($disp->ubicacion) — {{ $disp->ubicacion }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_dispositivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Archivo de marcas <span class="text-danger">*</span></label>
                        <input type="file" name="archivo" id="archivo" accept=".txt,.csv,.dat,.xls,.xlsx"
                               class="form-control form-control-sm @error('archivo') is-invalid @enderror" required>
                        <div class="form-text small">Formatos aceptados: TXT, CSV, DAT, XLS, XLSX. Máx. 10MB.</div>
                        @error('archivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                            <i class="bi bi-upload me-1"></i> Importar
                        </button>
                        <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Instrucciones de formato
            </div>
            <div class="card-body small">
                <p class="fw-semibold mb-1">Formato TXT / DAT (reloj biométrico)</p>
                <p class="text-muted">Cada línea debe contener los campos separados por tabulación o coma:</p>
                <div class="bg-light border rounded p-2 font-monospace small mb-3" style="font-size:.75rem">
                    ID_EMPLEADO&nbsp;&nbsp;FECHA&nbsp;&nbsp;HORA&nbsp;&nbsp;TIPO<br>
                    12345&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2026-03-31&nbsp;&nbsp;08:02&nbsp;&nbsp;0<br>
                    12345&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2026-03-31&nbsp;&nbsp;17:10&nbsp;&nbsp;1
                </div>

                <p class="fw-semibold mb-1">Formato CSV</p>
                <p class="text-muted">Primera fila como cabecera. Columnas requeridas:</p>
                <ul class="text-muted mb-3 ps-3">
                    <li><code>documento</code> — DNI del empleado</li>
                    <li><code>fecha_hora</code> — Fecha y hora (YYYY-MM-DD HH:MM)</li>
                    <li><code>tipo_captura</code> — <code>entrada</code> o <code>salida</code></li>
                </ul>

                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    El sistema asignará automáticamente el dispositivo seleccionado a todos los registros del archivo.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
