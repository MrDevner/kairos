@extends('layouts.app')

@section('title', 'Declaraciones Juradas')

@section('breadcrumb')
    <li class="breadcrumb-item active">DDJJ</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-file-earmark-text-fill me-1"></i> Declaraciones Juradas
    </h5>
    <a href="{{ route('ddjj.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nueva DDJJ
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('ddjj.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <select name="estado" class="form-select form-select-sm">
                    <option value="">— Todos los estados —</option>
                    <option value="borrador"   @selected(request('estado') === 'borrador')>Borrador</option>
                    <option value="presentada" @selected(request('estado') === 'presentada')>Presentada</option>
                    <option value="aprobada"   @selected(request('estado') === 'aprobada')>Aprobada</option>
                    <option value="rechazada"  @selected(request('estado') === 'rechazada')>Rechazada</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('ddjj.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
        <i class="bi bi-list-ul me-2"></i> Listado
        <span class="badge bg-light text-dark ms-auto">{{ $ddjjs->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Cargo / Designación</th>
                        <th>Período</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ddjjs as $ddjj)
                        @php
                            $estadoBadge = [
                                'borrador'   => 'secondary',
                                'presentada' => 'warning',
                                'aprobada'   => 'success',
                                'rechazada'  => 'danger',
                            ][$ddjj->estado] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('usuarios.show', $ddjj->designacion->usuario) }}" class="text-decoration-none">
                                    {{ $ddjj->designacion->usuario->nombre_completo ?? '—' }}
                                </a>
                            </td>
                            <td class="small">{{ $ddjj->designacion->cargo->nombre ?? '—' }}</td>
                            <td class="small">
                                {{ \Carbon\Carbon::parse($ddjj->fecha_inicio)->format('d/m/Y') }}
                                — {{ \Carbon\Carbon::parse($ddjj->fecha_fin)->format('d/m/Y') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $estadoBadge }}">{{ ucfirst($ddjj->estado) }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('ddjj.show', $ddjj) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($ddjj->estado === 'borrador')
                                    <a href="{{ route('ddjj.edit', $ddjj) }}" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('ddjj.destroy', $ddjj) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta declaración jurada?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron declaraciones juradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ddjjs->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $ddjjs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
