@extends('layouts.app')
@section('title', 'Importar marcas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas del personal</a></li>
    <li class="breadcrumb-item active">Importar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-upload me-1"></i> Importar marcas
    </h5>
    <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

@if(session('errores_importacion'))
    <div class="alert alert-warning">
        <strong><i class="bi bi-exclamation-triangle me-1"></i> Líneas con error:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach(session('errores_importacion') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Cargar archivo de marcas
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('marcas.importar.post') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Dispositivo origen <span class="text-danger">*</span></label>
                        <select name="id_dispositivo"
                                class="form-select form-select-sm @error('id_dispositivo') is-invalid @enderror"
                                required>
                            <option value="">— Seleccione un dispositivo —</option>
                            @foreach($dispositivos as $disp)
                                <option value="{{ $disp->id }}" @selected(old('id_dispositivo') == $disp->id)>
                                    {{ $disp->nombre }}
                                    @if($disp->institucion) — {{ $disp->institucion->sigla ?? $disp->institucion->nombre }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_dispositivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Archivo de marcas <span class="text-danger">*</span></label>
                        <input type="file" name="archivo" accept=".txt,.csv,.dat"
                               class="form-control form-control-sm @error('archivo') is-invalid @enderror"
                               required>
                        <div class="form-text small">Formatos aceptados: TXT, CSV, DAT. Máx. 10 MB.</div>
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
                <i class="bi bi-info-circle me-1"></i> Formato esperado
            </div>
            <div class="card-body small">
                <p class="fw-semibold mb-1">Formato CSV / TXT</p>
                <p class="text-muted mb-1">Una marca por línea. Columnas separadas por coma:</p>
                <div class="bg-light border rounded p-2 font-monospace mb-3" style="font-size:.75rem">
                    <span class="text-muted">documento,fecha_hora</span><br>
                    12345678,2026-04-27 08:02:00<br>
                    12345678,2026-04-27 17:15:00<br>
                    87654321,2026-04-27 07:55:00
                </div>

                <p class="fw-semibold mb-1">Campos requeridos</p>
                <ul class="text-muted mb-3 ps-3">
                    <li><code>documento</code> — DNI del empleado</li>
                    <li><code>fecha_hora</code> — Fecha y hora (YYYY-MM-DD HH:MM:SS)</li>
                </ul>

                <p class="fw-semibold mb-1">Notas</p>
                <ul class="text-muted mb-0 ps-3">
                    <li>Las marcas se importarán con tipo <strong>importada</strong>.</li>
                    <li>Después de importar, use <strong>Procesar marcas</strong> para calcular entradas/salidas.</li>
                    <li>Líneas con documento no encontrado o fecha inválida se ignoran y se reportan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
