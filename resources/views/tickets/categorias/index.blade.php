@extends('layouts.app')

@section('title', 'Categorías de tickets')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">Categorías</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)"><i class="bi bi-tags-fill me-1"></i> Categorías de tickets</h5>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">Nueva categoría</div>
            <div class="card-body">
                <form method="POST" action="{{ route('tickets.categorias.store') }}">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                               placeholder="Nombre" value="{{ old('nombre') }}" required maxlength="100">
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-sm w-100" style="background:var(--azul);color:#fff">
                        <i class="bi bi-plus-lg me-1"></i> Crear
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-body p-0">
                @forelse($categorias as $cat)
                <div class="d-flex align-items-center gap-2 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <form method="POST" action="{{ route('tickets.categorias.update', $cat) }}" class="d-flex align-items-center gap-2 flex-grow-1 mb-0">
                        @csrf @method('PUT')
                        <input type="text" name="nombre" value="{{ $cat->nombre }}" class="form-control form-control-sm" style="max-width:200px"
                               {{ $cat->trashed() ? 'disabled' : '' }}>
                        <span class="text-muted small font-monospace d-none d-md-inline">{{ $cat->slug }}</span>

                        @if($cat->trashed())
                            <span class="badge bg-secondary">Eliminada</span>
                        @else
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" name="activo" value="1" class="form-check-input" @checked($cat->activo)>
                                <label class="form-check-label small">Activa</label>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Guardar</button>
                        @endif
                    </form>

                    @if(!$cat->trashed())
                        <form method="POST" action="{{ route('tickets.categorias.destroy', $cat) }}" class="mb-0"
                              onsubmit="return confirm('¿Eliminar esta categoría?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('tickets.categorias.restore', $cat->id) }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restaurar"><i class="bi bi-arrow-counterclockwise"></i></button>
                        </form>
                    @endif
                </div>
                @empty
                    <div class="p-4 text-center text-muted small">Sin categorías todavía.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
