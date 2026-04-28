@extends('layouts.app')
@section('title', 'Editar marca')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas del personal</a></li>
    <li class="breadcrumb-item active">Editar marca</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-pencil-square me-1"></i> Editar marca
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('marcas.show', $marca) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye me-1"></i> Ver
        </a>
        <a href="{{ route('marcas.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-fingerprint me-1"></i> Datos de la marca
            </div>
            <div class="card-body">
                @if($marca->procesada)
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Esta marca ya fue procesada. Al actualizarla se marcará nuevamente como <strong>sin procesar</strong> para que sea reprocesada.
                    </div>
                @endif

                <form method="POST" action="{{ route('marcas.update', $marca) }}">
                    @csrf @method('PUT')
                    @include('marcas._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
