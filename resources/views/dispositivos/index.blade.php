@extends('layouts.app')

@section('title', 'Dispositivos')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dispositivos</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-hdd-network-fill me-1"></i> Dispositivos
    </h5>
    <a href="{{ route('dispositivos.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo dispositivo
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('dispositivos.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <select name="institucion" class="form-select form-select-sm">
                    <option value="">— Todas las instituciones —</option>
                    @foreach($instituciones as $inst)
                        <option value="{{ $inst->id }}" @selected(request('institucion') == $inst->id)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">— Todos los tipos —</option>
                    <option value="biometrico" @selected(request('tipo') === 'biometrico')>Biométrico</option>
                    <option value="web"        @selected(request('tipo') === 'web')>Web</option>
                    <option value="otro"       @selected(request('tipo') === 'otro')>Otro</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
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
        <span class="badge bg-light text-dark ms-auto">{{ $dispositivos->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Institución</th>
                        <th>Tipo</th>
                        <th>Modo conexión</th>
                        <th>IP</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispositivos as $disp)
                        <tr>
                            <td class="fw-semibold">{{ $disp->nombre }}</td>
                            <td class="small">{{ $disp->institucion->nombre ?? '—' }}</td>
                            <td>
                                @php
                                    $tipoMap  = ['biometrico' => ['Biométrico','secondary'], 'web' => ['Web','info'], 'otro' => ['Otro','light']];
                                    [$tipoLabel, $tipoColor] = $tipoMap[$disp->tipo] ?? [$disp->tipo, 'light'];
                                @endphp
                                <span class="badge bg-{{ $tipoColor }} text-dark">{{ $tipoLabel }}</span>
                            </td>
                            <td class="small">
                                @php
                                    $modoMap = ['directo_bd' => 'Directo a BD', 'importacion' => 'Importación', 'web' => 'Web'];
                                @endphp
                                {{ $modoMap[$disp->modo_conexion] ?? $disp->modo_conexion }}
                            </td>
                            <td class="small font-monospace">{{ $disp->ip_address ?? '—' }}</td>
                            <td class="text-center">
                                @if($disp->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('dispositivos.show', $disp) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('dispositivos.edit', $disp) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('dispositivos.destroy', $disp) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar «{{ addslashes($disp->nombre) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No se encontraron dispositivos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($dispositivos->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $dispositivos->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
