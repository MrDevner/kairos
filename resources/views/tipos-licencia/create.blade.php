@extends('layouts.app')

@section('title', 'Nuevo tipo de licencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tipos-licencia.index') }}">Tipos de licencia</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-plus-circle me-1"></i> Nuevo tipo de licencia
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tipos-licencia.store') }}">
                    @csrf
                    @include('tipos-licencia._form')
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                            <i class="bi bi-check-lg me-1"></i> Guardar
                        </button>
                        <a href="{{ route('tipos-licencia.index') }}" class="btn btn-sm btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
