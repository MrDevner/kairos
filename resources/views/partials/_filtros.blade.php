{{-- Partial: barra de búsqueda y filtros genérica --}}
{{-- Uso: @include('partials._filtros', ['action' => route('...'), 'campos' => [...]]) --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ $action }}" class="row g-2 align-items-end">
            {{ $slot ?? '' }}
            <div class="col-auto">
                <button type="submit" class="btn btn-sm" style="background:var(--azul);color:#fff">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>
