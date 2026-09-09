@extends('layouts.app')

@section('title', 'Nuevo Dispositivo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dispositivos.index') }}">Dispositivos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="k2-page-title">
        <i class="bi bi-hdd-network me-1"></i> Nuevo Dispositivo
    </h5>
    <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card" style="max-width:680px">
    <div class="card-header">
        <i class="bi bi-hdd-network me-1"></i> Datos del dispositivo
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('dispositivos.store') }}">
            @csrf
            @include('dispositivos._form', ['instituciones' => $instituciones])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Guardar
                </button>
                <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
