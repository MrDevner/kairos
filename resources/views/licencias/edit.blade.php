@extends('layouts.app')

@section('title', 'Editar licencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('licencias.index') }}">Licencias</a></li>
    <li class="breadcrumb-item"><a href="{{ route('licencias.show', $licencia) }}">{{ $licencia->usuario->nombre_completo ?? 'Licencia' }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-pencil-square me-1"></i> Editar licencia
                <span class="badge bg-warning text-dark ms-2">Pendiente</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('licencias.update', $licencia) }}">
                    @csrf @method('PUT')
                    @include('licencias._form')
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                            <i class="bi bi-check-lg me-1"></i> Guardar cambios
                        </button>
                        <a href="{{ route('licencias.show', $licencia) }}" class="btn btn-sm btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
