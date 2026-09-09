{{--
    Menú principal del layout.
    Variables esperadas:
      $menu        → estructura definida en layouts/app.blade.php
      $modo        → 'desktop' (barra horizontal con dropdowns) | 'mobile' (lista vertical del offcanvas)
      $instActiva  → (solo mobile) institución activa, para el acceso al dashboard institucional
--}}
@php
    $esActivo = function (array $item) {
        if (array_key_exists('activo', $item)) {
            return (bool) $item['activo'];
        }
        return !empty($item['active']) && request()->routeIs(...$item['active']);
    };
@endphp

@if($modo === 'desktop')
    <ul class="k2-nav">
        @foreach($menu as $grupo)
            @if(isset($grupo['items']))
                @php $grupoBadge = array_sum(array_map(fn ($i) => (int) ($i['badge'] ?? 0), $grupo['items'])); @endphp
                <li class="dropdown">
                    <a class="k2-nav-link dropdown-toggle {{ $esActivo($grupo) ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi {{ $grupo['icon'] }}"></i>
                        <span>{{ $grupo['label'] }}</span>
                        @if($grupoBadge > 0)
                            <span class="k2-badge danger" style="padding:.1rem .45rem">{{ $grupoBadge }}</span>
                        @endif
                        <i class="bi bi-chevron-down k2-caret"></i>
                    </a>
                    <div class="dropdown-menu k2-dropdown {{ ($grupo['cols'] ?? 1) > 1 ? 'k2-dropdown-wide' : '' }}">
                        <div class="k2-dropdown-head">{{ $grupo['label'] }}</div>
                        <div class="{{ ($grupo['cols'] ?? 1) > 1 ? 'k2-dropdown-grid' : '' }}">
                            @foreach($grupo['items'] as $item)
                                <a class="k2-dropdown-item {{ $esActivo($item) ? 'active' : '' }} {{ !empty($item['danger']) ? 'danger' : '' }}"
                                   href="{{ $item['url'] }}"
                                   @if(!empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif>
                                    <span class="k2-dropdown-icon"><i class="bi {{ $item['icon'] }}"></i></span>
                                    <span class="k2-dropdown-text">
                                        <span class="k2-dropdown-label">{{ $item['label'] }}</span>
                                        @if(!empty($item['desc']))
                                            <span class="k2-dropdown-desc">{{ $item['desc'] }}</span>
                                        @endif
                                    </span>
                                    @if(($item['badge'] ?? 0) > 0)
                                        <span class="k2-badge danger ms-auto">{{ $item['badge'] }}</span>
                                    @elseif(!empty($item['external']))
                                        <i class="bi bi-box-arrow-up-right k2-dropdown-ext"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </li>
            @else
                <li>
                    <a class="k2-nav-link {{ $esActivo($grupo) ? 'active' : '' }}" href="{{ $grupo['url'] }}">
                        <i class="bi {{ $grupo['icon'] }}"></i>
                        <span>{{ $grupo['label'] }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>

@else
    @foreach($menu as $grupo)
        @if(isset($grupo['items']))
            <div class="k2-mnav-group">{{ $grupo['label'] }}</div>
            @foreach($grupo['items'] as $item)
                <a class="k2-mnav-link {{ $esActivo($item) ? 'active' : '' }}"
                   href="{{ $item['url'] }}"
                   @if(!empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif>
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    @if(($item['badge'] ?? 0) > 0)
                        <span class="k2-badge danger ms-auto">{{ $item['badge'] }}</span>
                    @elseif(!empty($item['external']))
                        <i class="bi bi-box-arrow-up-right ms-auto" style="font-size:.7rem;opacity:.6"></i>
                    @endif
                </a>
            @endforeach
        @else
            <div class="k2-mnav-group">General</div>
            <a class="k2-mnav-link {{ $esActivo($grupo) ? 'active' : '' }}" href="{{ $grupo['url'] }}">
                <i class="bi {{ $grupo['icon'] }}"></i>
                <span>{{ $grupo['label'] }}</span>
            </a>
            @if(!empty($instActiva))
                <a class="k2-mnav-link {{ request()->routeIs('home.institucion') ? 'active' : '' }}" href="{{ route('home.institucion') }}">
                    <i class="bi bi-building"></i>
                    <span>Dashboard institucional</span>
                </a>
            @endif
        @endif
    @endforeach

    <div class="k2-mnav-group">Cuenta</div>
    <a class="k2-mnav-link {{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}">
        <i class="bi bi-person-circle"></i><span>Mi perfil</span>
    </a>
    <a class="k2-mnav-link" href="{{ route('perfil') }}#metodos-login">
        <i class="bi bi-key"></i><span>Métodos de acceso</span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="k2-mnav-link border-0 bg-transparent" style="color:var(--k2-danger-text);width:calc(100% - 1rem)">
            <i class="bi bi-box-arrow-right" style="color:inherit"></i><span>Cerrar sesión</span>
        </button>
    </form>
@endif
