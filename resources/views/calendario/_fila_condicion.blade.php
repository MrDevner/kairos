@php
    $tc     = old("condiciones.{$i}.tipo_condicion",  $cond?->tipo_condicion  ?? 'sexo');
    $ef     = old("condiciones.{$i}.efecto",           $cond?->efecto          ?? '');
    $min    = old("condiciones.{$i}.minutos_afectados",$cond?->minutos_afectados ?? '');
    $esParo = $esParo ?? false;
@endphp
<div class="fila-condicion card mb-2 p-2" data-idx="{{ $i }}">
    <div class="row g-2 align-items-end">

        {{-- Tipo condición --}}
        <div class="{{ $esParo ? 'col-md-5' : 'col-md-3' }}">
            <label class="form-label small mb-1">{{ $esParo ? 'Filtrar por' : 'Condición' }}</label>
            <select name="condiciones[{{ $i }}][tipo_condicion]"
                    class="form-select form-select-sm tipo-condicion-select">
                <option value="sexo"            @selected($tc === 'sexo')>Sexo</option>
                <option value="cargo"           @selected($tc === 'cargo')>Cargo</option>
                <option value="categoria_cargo" @selected($tc === 'categoria_cargo')>Categoría de cargo</option>
                <option value="dependencia"     @selected($tc === 'dependencia')>Dependencia</option>
                <option value="custom"          @selected($tc === 'custom')>Personalizado</option>
            </select>
        </div>

        {{-- Valor (dinámico por JS) --}}
        <div class="{{ $esParo ? 'col-md-6' : 'col-md-3' }}">
            <label class="form-label small mb-1">Valor</label>
            <div class="valor-condicion-wrapper">
                <input type="hidden" class="valor-inicial" value="{{ old("condiciones.{$i}.valor_condicion", $cond?->valor_condicion ?? '') }}">
            </div>
        </div>

        @if(!$esParo)
        {{-- Efecto (solo para evento_condicional) --}}
        <div class="col-md-3">
            <label class="form-label small mb-1">Efecto</label>
            <select name="condiciones[{{ $i }}][efecto]"
                    class="form-select form-select-sm efecto-select">
                <option value="retiro_anticipado" @selected($ef === 'retiro_anticipado')>Retiro anticipado</option>
                <option value="ingreso_tardio"    @selected($ef === 'ingreso_tardio')>Ingreso tardío</option>
                <option value="jornada_reducida"  @selected($ef === 'jornada_reducida')>Jornada reducida</option>
                <option value="exencion"          @selected($ef === 'exencion')>Exención total</option>
            </select>
        </div>

        {{-- Minutos --}}
        <div class="col-md-2 minutos-wrapper" @if($ef === 'exencion') style="display:none" @endif>
            <label class="form-label small mb-1">Minutos afectados</label>
            <input type="number" name="condiciones[{{ $i }}][minutos_afectados]"
                   class="form-control form-control-sm"
                   value="{{ $min }}" min="1" placeholder="ej: 60">
        </div>
        @endif

        {{-- Eliminar --}}
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-condicion" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>

    </div>
</div>
