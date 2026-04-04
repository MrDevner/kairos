@extends('layouts.app')

@section('title', 'Nueva licencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('licencias.index') }}">Licencias</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-plus-circle me-1"></i> Nueva licencia
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('licencias.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('licencias._form')
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                            <i class="bi bi-check-lg me-1"></i> Registrar
                        </button>
                        <a href="{{ route('licencias.index') }}" class="btn btn-sm btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
