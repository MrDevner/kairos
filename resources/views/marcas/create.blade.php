@extends('layouts.app')
@section('title', 'Nueva marca')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas del personal</a></li>
    <li class="breadcrumb-item active">Nueva marca</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-plus-circle me-1"></i> Nueva marca
    </h5>
    <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-fingerprint me-1"></i> Datos de la marca
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('marcas.store') }}">
                    @csrf
                    @include('marcas._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
