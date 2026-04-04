@extends('layouts.app')
@section('title', 'Editar institución')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('instituciones.index') }}">Instituciones</a></li>
    <li class="breadcrumb-item active">{{ $institucion->nombre }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil me-2"></i>Editar: {{ $institucion->nombre }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('instituciones.update', $institucion) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    @include('instituciones._form', ['padres' => $padres, 'model' => $institucion])
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn" style="background:var(--azul);color:#fff">
                            <i class="bi bi-check-lg"></i> Actualizar
                        </button>
                        <a href="{{ route('instituciones.show', $institucion) }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
