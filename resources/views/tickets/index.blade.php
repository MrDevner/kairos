@extends('layouts.app')

@section('title', 'Tickets de soporte')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tickets</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-life-preserver me-1"></i> Tickets de soporte
        @if($puedeVerTodos)<span class="badge bg-secondary fw-normal">todos</span>@endif
    </h5>
    <a href="{{ route('tickets.create') }}" class="btn btn-sm" style="background:var(--azul);color:#fff">
        <i class="bi bi-plus-lg me-1"></i> Nuevo ticket
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('tickets.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">— Todos —</option>
                    @foreach(\App\Models\Ticket::ESTADOS as $e)
                        <option value="{{ $e }}" @selected(request('estado') === $e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-0 small">Prioridad</label>
                <select name="prioridad" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    @foreach(\App\Models\Ticket::PRIORIDADES as $p)
                        <option value="{{ $p }}" @selected(request('prioridad') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Categoría</label>
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c }}" @selected(request('categoria') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-0 small">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Título o descripción…">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff"><i class="bi bi-search"></i></button>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($tickets->isEmpty())
            <div class="p-4 text-center text-muted small">
                <i class="bi bi-inbox d-block mb-1 fs-4"></i>
                No hay tickets para mostrar.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th class="text-center">Prioridad</th>
                            <th class="text-center">Estado</th>
                            <th>Creador</th>
                            <th>Asignado</th>
                            <th>Actualizado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'" style="cursor:pointer">
                            <td class="fw-semibold">{{ Str::limit($ticket->titulo, 50) }}</td>
                            <td>{{ $ticket->categoria }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ ['baja'=>'secondary','media'=>'info','alta'=>'warning','urgente'=>'danger'][$ticket->prioridad] ?? 'secondary' }}">
                                    {{ ucfirst($ticket->prioridad) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ ['abierto'=>'danger','en_proceso'=>'warning','resuelto'=>'info','cerrado'=>'success'][$ticket->estado] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_',' ',$ticket->estado)) }}
                                </span>
                            </td>
                            <td>{{ $ticket->creador?->nombre_completo }}</td>
                            <td>{{ $ticket->asignadoA?->nombre_completo ?? '—' }}</td>
                            <td class="text-muted">{{ $ticket->updated_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center small text-muted">
                <span>Mostrando {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} de {{ $tickets->total() }} registros</span>
                {{ $tickets->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
