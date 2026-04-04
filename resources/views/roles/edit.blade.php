@extends('layouts.app')

@section('title', 'Editar rol: ' . $rol->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.show', $rol) }}">{{ $rol->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-shield-check me-1"></i> Editar rol: <span class="fw-normal">{{ $rol->nombre }}</span>
    </h5>
    <a href="{{ route('roles.show', $rol) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:860px">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $rol) }}">
            @csrf @method('PUT')
            @include('roles._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Actualizar rol
                </button>
                <a href="{{ route('roles.show', $rol) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
