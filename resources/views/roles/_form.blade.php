@php $rol = $rol ?? null; @endphp

@php
    $nivelActor = $nivelActor ?? 0;
    $nivelMin   = $nivelActor > 0 ? $nivelActor + 1 : 1;
@endphp

{{-- Datos básicos --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre"
               class="form-control form-control-sm @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $rol?->nombre) }}" required maxlength="255" autofocus>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold small">
            Nivel jerárquico <span class="text-danger">*</span>
            <i class="bi bi-info-circle text-muted" title="Número menor = mayor autoridad. Solo puede asignar roles con nivel mayor al propio."></i>
        </label>
        <input type="number" name="nivel"
               class="form-control form-control-sm @error('nivel') is-invalid @enderror"
               value="{{ old('nivel', $rol?->nivel ?? 100) }}"
               min="{{ $nivelMin }}" max="999" required>
        @error('nivel')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($nivelActor > 0)
            <div class="form-text small text-muted">Mínimo permitido: {{ $nivelMin }} (su nivel: {{ $nivelActor }})</div>
        @else
            <div class="form-text small text-muted">
                Referencia: Dir. Institución=10, Dir. Administrativo=20, Jefe Personal=30, Dep. Personal=40
            </div>
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold small">Estado</label>
        <div class="form-check mt-2">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1"
                   class="form-check-input" id="activo"
                   @checked(old('activo', $rol?->activo ?? true))>
            <label class="form-check-label small" for="activo">Rol activo</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold small">Descripción</label>
        <textarea name="descripcion" rows="2"
                  class="form-control form-control-sm @error('descripcion') is-invalid @enderror"
                  maxlength="500" placeholder="Descripción opcional del rol…">{{ old('descripcion', $rol?->descripcion) }}</textarea>
        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<hr>

{{-- Matriz de permisos --}}
<h6 class="fw-semibold mb-3" style="color:var(--azul)">
    <i class="bi bi-key me-1"></i> Permisos por módulo
</h6>

<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0" id="tabla-permisos">
        <thead class="table-light text-center">
            <tr>
                <th class="text-start" style="width:30%">Módulo</th>
                <th style="width:16%">
                    <i class="bi bi-eye me-1"></i>Ver
                </th>
                <th style="width:18%">
                    <i class="bi bi-plus-circle me-1"></i>Crear
                </th>
                <th style="width:18%">
                    <i class="bi bi-pencil me-1"></i>Editar
                </th>
                <th style="width:18%">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </th>
            </tr>
            <tr class="table-secondary text-center small">
                <td class="text-start text-muted fst-italic">Marcar/desmarcar todo</td>
                @foreach(['ver','crear','editar','eliminar'] as $col)
                    <td>
                        <input type="checkbox" class="form-check-input toggle-col" data-col="{{ $col }}"
                               title="Marcar/desmarcar columna {{ $col }}">
                    </td>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($modulos as $clave => $etiqueta)
                @php
                    $p = $permisosIndexados[$clave] ?? null;
                @endphp
                <tr>
                    <td class="fw-semibold small">{{ $etiqueta }}</td>
                    @foreach(['ver','crear','editar','eliminar'] as $accion)
                        @php
                            $checked = old("permisos.{$clave}", []);
                            if (is_array($checked)) {
                                $isChecked = in_array($accion, $checked);
                            } else {
                                $isChecked = false;
                            }
                            // Si no hay old input (primera carga en edit), usar valores del modelo
                            if (!session()->hasOldInput() && $p !== null) {
                                $isChecked = $p->{"puede_{$accion}"};
                            }
                        @endphp
                        <td class="text-center">
                            <input type="checkbox"
                                   class="form-check-input perm-check perm-{{ $accion }}"
                                   name="permisos[{{ $clave }}][]"
                                   value="{{ $accion }}"
                                   @checked($isChecked)>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
document.querySelectorAll('.toggle-col').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const col = this.dataset.col;
        document.querySelectorAll(`.perm-${col}`).forEach(cb => {
            cb.checked = this.checked;
        });
    });
});
</script>
@endpush
