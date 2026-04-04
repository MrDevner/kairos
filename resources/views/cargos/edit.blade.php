@extends('layouts.app')
@section('title', 'Editar cargo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cargos.index') }}">Cargos</a></li>
    <li class="breadcrumb-item active">{{ $cargo->nombre }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-pencil me-1"></i> Editar cargo
    </h5>
    <a href="{{ route('cargos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST" action="{{ route('cargos.update', $cargo) }}">
            @csrf @method('PUT')
            @include('cargos._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-save me-1"></i> Guardar cambios
                </button>
                <a href="{{ route('cargos.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
