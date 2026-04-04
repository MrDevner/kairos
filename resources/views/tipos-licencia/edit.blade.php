@extends('layouts.app')

@section('title', 'Editar tipo de licencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tipos-licencia.index') }}">Tipos de licencia</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tipos-licencia.show', $tipo) }}">{{ $tipo->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-pencil-square me-1"></i> Editar: {{ $tipo->nombre }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tipos-licencia.update', $tipo) }}">
                    @csrf @method('PUT')
                    @include('tipos-licencia._form')
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                            <i class="bi bi-check-lg me-1"></i> Guardar cambios
                        </button>
                        <a href="{{ route('tipos-licencia.show', $tipo) }}" class="btn btn-sm btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
