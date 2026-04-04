@extends('layouts.app')

@section('title', 'Nuevo rol')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Nuevo rol</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-shield-plus me-1"></i> Nuevo rol
    </h5>
    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:860px">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            @include('roles._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Guardar rol
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
