{{-- Fila recursiva del árbol de instituciones --}}
<tr>
    <td>
        <span style="padding-left: {{ $nivel * 1.5 }}rem">
            @if($nivel > 0)<i class="bi bi-arrow-return-right text-muted me-1"></i>@endif
            @if($inst->hijasRecursivas->isNotEmpty())
                <button class="btn btn-link btn-sm p-0 me-1 toggle-hijos"
                        data-target="hijos-{{ $inst->id }}" title="Expandir/colapsar">
                    <i class="bi bi-chevron-down"></i>
                </button>
            @endif
            <strong>{{ $inst->nombre }}</strong>
        </span>
    </td>
    <td><span class="badge bg-secondary">{{ $inst->sigla ?? '—' }}</span></td>
    <td>{{ $inst->padre?->nombre ?? '—' }}</td>
    <td>
        @if($inst->activa)
            <span class="badge bg-success">Activa</span>
        @else
            <span class="badge bg-secondary">Inactiva</span>
        @endif
    </td>
    <td class="text-end">
        <a href="{{ route('instituciones.show', $inst) }}" class="btn btn-sm btn-outline-secondary btn-sm" title="Ver"><i class="bi bi-eye"></i></a>
        @if(auth()->user()->hasRole('Administrador General'))
            <a href="{{ route('instituciones.edit', $inst) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="{{ route('instituciones.destroy', $inst) }}" class="d-inline"
                  onsubmit="return confirm('¿Eliminar esta institución?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </td>
</tr>

@if($inst->hijasRecursivas->isNotEmpty())
    <tr id="hijos-{{ $inst->id }}" class="fila-hijos">
        <td colspan="5" class="p-0">
            <table class="table mb-0">
                <tbody>
                    @foreach($inst->hijasRecursivas as $hija)
                        @include('instituciones._fila', ['inst' => $hija, 'nivel' => $nivel + 1])
                    @endforeach
                </tbody>
            </table>
        </td>
    </tr>
@endif

@once
@push('scripts')
<script>
document.querySelectorAll('.toggle-hijos').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        const icon   = this.querySelector('i');
        target.classList.toggle('d-none');
        icon.classList.toggle('bi-chevron-down');
        icon.classList.toggle('bi-chevron-right');
    });
});
</script>
@endpush
@endonce
