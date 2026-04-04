@extends('layouts.app')

@section('title', 'Nuevo evento')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('calendario.index') }}">Calendario</a></li>
    <li class="breadcrumb-item active">Nuevo evento</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-calendar-plus me-1"></i> Nuevo evento
    </h5>
    <a href="{{ route('calendario.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:900px">
    <div class="card-body">
        <form method="POST" action="{{ route('calendario.store') }}">
            @csrf
            @include('calendario._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Guardar evento
                </button>
                <a href="{{ route('calendario.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
