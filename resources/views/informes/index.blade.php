@extends('layouts.app')

@section('title', 'Informes')

@section('breadcrumb')
    <li class="breadcrumb-item active">Informes</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-file-earmark-bar-graph-fill me-1"></i> Informes de asistencia
    </h5>
</div>

{{-- Generar informe --}}
<div class="card mb-3">
    <div class="card-header" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-circle me-1"></i> Generar nuevo informe
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('informes.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm-4">
                <label class="form-label fw-semibold small">Institución <span class="text-danger">*</span></label>
                <select name="id_institucion" class="form-select form-select-sm @error('id_institucion') is-invalid @enderror" required>
                    <option value="">— Seleccione —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(old('id_institucion') == $inst->id)>{{ $inst->nombre }}</option>
                    @endforeach
                </select>
                @error('id_institucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-3">
                <label class="form-label fw-semibold small">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" value="{{ old('fecha', today()->toDateString()) }}"
                       class="form-control form-control-sm @error('fecha') is-invalid @enderror" required>
                @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-gear me-1"></i> Generar informe
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('informes.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <select name="institucion" class="form-select form-select-sm">
                    <option value="">— Todas las instituciones —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(request('institucion') == $inst->id)>{{ $inst->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm" placeholder="Desde">
            </div>
            <div class="col-sm-2">
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm" placeholder="Hasta">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('informes.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Informes generados
        <span class="badge bg-light text-dark ms-auto">{{ $informes->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Institución</th>
                        <th>Fecha</th>
                        <th class="text-center">Estado</th>
                        <th>Generado</th>
                        <th>Por</th>
                        <th class="text-center" style="width:130px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($informes as $inf)
                        @php
                            $estadoBadge = [
                                'pendiente'  => 'warning',
                                'generado'   => 'success',
                                'error'      => 'danger',
                            ][$inf->estado] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>{{ $inf->institucion->nombre ?? '—' }}</td>
                            <td class="small">{{ $inf->fecha ? \Carbon\Carbon::parse($inf->fecha)->format('d/m/Y') : '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $estadoBadge }}">{{ ucfirst($inf->estado) }}</span>
                            </td>
                            <td class="small">
                                {{ $inf->created_at ? $inf->created_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="small">{{ $inf->generadoPor->nombre_completo ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('informes.show', $inf) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('informes.excel', $inf) }}" class="btn btn-sm btn-outline-success py-0 px-1" title="Excel">
                                    <i class="bi bi-file-earmark-excel"></i>
                                </a>
                                <a href="{{ route('informes.pdf', $inf) }}" class="btn btn-sm btn-outline-danger py-0 px-1" title="PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                <form method="POST" action="{{ route('informes.destroy', $inf) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este informe?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron informes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($informes->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $informes->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
