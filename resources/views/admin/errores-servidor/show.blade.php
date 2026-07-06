@extends('layouts.app')

@section('title', 'Detalle de error')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.errores-servidor.index') }}">Errores de servidor</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--azul)">
        <i class="bi bi-bug-fill me-1"></i> {{ $errorServidor->clase_error }}
    </h5>
    <a href="{{ route('admin.errores-servidor.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Detalle --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-info-circle me-1"></i> Detalle
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3 text-muted">Mensaje</dt>
                    <dd class="col-sm-9">{{ $errorServidor->mensaje_error }}</dd>

                    <dt class="col-sm-3 text-muted">Archivo</dt>
                    <dd class="col-sm-9 font-monospace">{{ $errorServidor->archivo }}:{{ $errorServidor->linea }}</dd>

                    <dt class="col-sm-3 text-muted">Endpoint</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-secondary">{{ $errorServidor->metodo_http }}</span>
                        {{ $errorServidor->endpoint }}
                    </dd>

                    <dt class="col-sm-3 text-muted">Usuario afectado</dt>
                    <dd class="col-sm-9">{{ $errorServidor->usuario?->nombre_completo ?? '— (no autenticado)' }}</dd>

                    <dt class="col-sm-3 text-muted">IP / Agente</dt>
                    <dd class="col-sm-9">{{ $errorServidor->direccion_ip }} — <span class="text-muted">{{ Str::limit($errorServidor->agente_usuario, 80) }}</span></dd>

                    <dt class="col-sm-3 text-muted">Correlation ID</dt>
                    <dd class="col-sm-9 font-monospace">{{ $errorServidor->id_correlacion }}</dd>

                    <dt class="col-sm-3 text-muted">Ocurrencias</dt>
                    <dd class="col-sm-9">{{ $errorServidor->cantidad_ocurrencias }} — primera: {{ $errorServidor->created_at->format('d/m/Y H:i') }}, última: {{ $errorServidor->ultima_ocurrencia_en?->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Parámetros de la solicitud --}}
        @if(!empty($errorServidor->parametros_solicitud))
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-braces me-1"></i> Parámetros de la solicitud
            </div>
            <div class="card-body p-0">
                <pre class="small mb-0 p-3" style="max-height:300px;overflow:auto">{{ json_encode($errorServidor->parametros_solicitud, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif

        {{-- Stack trace --}}
        <div class="card mb-3">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-list-columns-reverse me-1"></i> Stack trace
            </div>
            <div class="card-body p-0">
                <pre class="small mb-0 p-3" style="max-height:500px;overflow:auto">{{ $errorServidor->traza_pila }}</pre>
            </div>
        </div>

        {{-- Notas --}}
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-chat-left-text me-1"></i> Notas
            </div>
            <div class="card-body">
                @forelse(($errorServidor->notas ?? []) as $nota)
                    <div class="mb-2 pb-2 border-bottom small">
                        <div class="fw-semibold">{{ $nota['user_name'] ?? '—' }}
                            <span class="text-muted fw-normal">{{ \Illuminate\Support\Carbon::parse($nota['created_at'])->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>{{ $nota['content'] }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Sin notas todavía.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Triage --}}
        <div class="card">
            <div class="card-header" style="background:var(--azul);color:#fff">
                <i class="bi bi-clipboard-check me-1"></i> Triage
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.errores-servidor.update', $errorServidor) }}">
                    @csrf @method('PUT')

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            @foreach(['abierto','en_revision','mitigado','solucionado'] as $e)
                                <option value="{{ $e }}" @selected($errorServidor->estado === $e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Asignado a</label>
                        <select name="id_asignado_a" class="form-select form-select-sm">
                            <option value="">— Sin asignar —</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}" @selected($errorServidor->id_asignado_a === $u->id)>{{ $u->apellidos }}, {{ $u->nombres }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Agregar nota</label>
                        <textarea name="nota" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-sm w-100" style="background:var(--azul);color:#fff">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.errores-servidor.destroy', $errorServidor) }}"
                      class="mt-2" onsubmit="return confirm('¿Eliminar este registro definitivamente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Eliminar registro
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
