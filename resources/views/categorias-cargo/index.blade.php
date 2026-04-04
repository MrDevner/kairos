@extends('layouts.app')
@section('title', 'Categorías de cargo')

@section('breadcrumb')
    <li class="breadcrumb-item active">Categorías de cargo</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-tags me-1"></i> Categorías de cargo
    </h5>
    <a href="{{ route('cargos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-briefcase me-1"></i> Ver cargos
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 small">
        {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    {{-- Lista de categorías --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex align-items-center" style="background:var(--azul);color:#fff">
                <i class="bi bi-list-ul me-1"></i> Listado
                <span class="badge bg-light text-dark ms-auto">{{ $categorias->count() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0 align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th class="text-center">Cargos</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center" style="width:110px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categorias as $cat)
                            <tr>
                                <td class="fw-semibold">{{ $cat->nombre }}</td>
                                <td class="text-center">{{ $cat->cargos_count }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $cat->activo ? 'success' : 'secondary' }}">
                                        {{ $cat->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{-- Editar (modal inline) --}}
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-editar-{{ $cat->id }}"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    {{-- Eliminar --}}
                                    <form method="POST"
                                          action="{{ route('categorias-cargo.destroy', $cat) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar «{{ addslashes($cat->nombre) }}»?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Modal editar --}}
                            <div class="modal fade" id="modal-editar-{{ $cat->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('categorias-cargo.update', $cat) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-header" style="background:var(--azul);color:#fff">
                                                <h6 class="modal-title">Editar categoría</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Nombre</label>
                                                    <input type="text" name="nombre" value="{{ $cat->nombre }}"
                                                           class="form-control form-control-sm" required maxlength="100">
                                                </div>
                                                <div class="form-check">
                                                    <input type="hidden" name="activo" value="0">
                                                    <input type="checkbox" name="activo" value="1"
                                                           class="form-check-input" id="activo-{{ $cat->id }}"
                                                           @checked($cat->activo)>
                                                    <label class="form-check-label small" for="activo-{{ $cat->id }}">Activa</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer py-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                                                    <i class="bi bi-save me-1"></i> Guardar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox d-block fs-4 mb-1"></i> Sin categorías.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Formulario nueva categoría --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-plus-circle me-1"></i> Nueva categoría
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('categorias-cargo.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}"
                               class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                               required maxlength="100" placeholder="Ej: Contratado, Interino…">
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                        <i class="bi bi-save me-1"></i> Crear categoría
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
