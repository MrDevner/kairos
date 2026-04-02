@extends('layouts.app')
@section('title', 'Instituciones')
@section('breadcrumb')
    <li class="breadcrumb-item active">Instituciones</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-building me-2" style="color:var(--azul)"></i>Instituciones</h5>
    <a href="{{ route('instituciones.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg"></i> Nueva institución
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40%">Nombre</th>
                        <th>Sigla</th>
                        <th>Institución padre</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arbol as $inst)
                        @include('instituciones._fila', ['inst' => $inst, 'nivel' => 0])
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin instituciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
