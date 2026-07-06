{{-- Fila recursiva del árbol de instituciones --}}
<tr @if($nivel > 0) class="hijos-de-{{ $inst->padre_id }}" @endif>
    <td>
        <span style="padding-left: {{ $nivel * 1.5 }}rem">
            @if($nivel > 0)<i class="bi bi-arrow-return-right text-muted me-1"></i>@endif
            @if($inst->hijasRecursivas->isNotEmpty())
                <button class="btn btn-link btn-sm p-0 me-1 toggle-hijos"
                        data-target="hijos-de-{{ $inst->id }}" title="Expandir/colapsar">
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
        <a href="{{ route('instituciones.show', $inst) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="bi bi-eye"></i></a>
        @if(auth()->user()->permisos()->administrador()->tieneTodosLosPermisos())
            <a href="{{ route('instituciones.edit', $inst) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="{{ route('instituciones.destroy', $inst) }}" class="d-inline"
                  onsubmit="return confirm('¿Eliminar esta institución?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </td>
</tr>

@foreach($inst->hijasRecursivas as $hija)
    @include('instituciones._fila', ['inst' => $hija, 'nivel' => $nivel + 1])
@endforeach

@once
@push('scripts')
<script>
document.querySelectorAll('.toggle-hijos').forEach(btn => {
    btn.addEventListener('click', function () {
        const targetClass = this.dataset.target;
        const icon = this.querySelector('i');
        const isExpanded = icon.classList.contains('bi-chevron-down');

        if (isExpanded) {
            hideDescendants(targetClass);
            icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
        } else {
            document.querySelectorAll('tr.' + targetClass).forEach(r => r.classList.remove('d-none'));
            icon.classList.replace('bi-chevron-right', 'bi-chevron-down');
        }
    });
});

function hideDescendants(cls) {
    document.querySelectorAll('tr.' + cls).forEach(row => {
        row.classList.add('d-none');
        const btn = row.querySelector('.toggle-hijos');
        if (btn) {
            const icon = btn.querySelector('i');
            if (icon.classList.contains('bi-chevron-down')) {
                icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
                hideDescendants(btn.dataset.target);
            }
        }
    });
}
</script>
@endpush
@endonce
