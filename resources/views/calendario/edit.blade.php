@extends('layouts.app')

@section('title', 'Editar: ' . $calendario->titulo)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('calendario.index') }}">Calendario</a></li>
    <li class="breadcrumb-item"><a href="{{ route('calendario.show', $calendario) }}">{{ $calendario->titulo }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-calendar-check me-1"></i> Editar evento
    </h5>
    <a href="{{ route('calendario.show', $calendario) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:900px">
    <div class="card-body">
        <form method="POST" action="{{ route('calendario.update', $calendario) }}">
            @csrf @method('PUT')
            @php $evento = $calendario; @endphp
            @include('calendario._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-check-lg me-1"></i> Actualizar evento
                </button>
                <a href="{{ route('calendario.show', $calendario) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
