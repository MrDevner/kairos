@extends('layouts.app')
@section('title', 'Nueva institución')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('instituciones.index') }}">Instituciones</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-building me-2"></i>Nueva institución</div>
            <div class="card-body">
                <form method="POST" action="{{ route('instituciones.store') }}">
                    @csrf
                    @include('instituciones._form', ['padres' => $padres])
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn" style="background:var(--azul);color:#fff">
                            <i class="bi bi-check-lg"></i> Guardar
                        </button>
                        <a href="{{ route('instituciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
