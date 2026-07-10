@php $instId = session('institucion_activa_id'); @endphp

<li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('dependencias.*') ? 'active' : '' }}" href="{{ route('dependencias.index') }}"><i class="bi bi-diagram-3"></i> Dependencias</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('edificios.*') ? 'active' : '' }}" href="{{ route('edificios.index') }}"><i class="bi bi-building"></i> Edificios</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('oficinas.*') ? 'active' : '' }}" href="{{ route('oficinas.index') }}"><i class="bi bi-door-open"></i> Oficinas</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}"><i class="bi bi-people"></i> Personal</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('designaciones.*') ? 'active' : '' }}" href="{{ route('designaciones.index') }}"><i class="bi bi-briefcase"></i> Designaciones</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('marcas.*') ? 'active' : '' }}" href="{{ route('marcas.index') }}"><i class="bi bi-fingerprint"></i> Marcas</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}"><i class="bi bi-calendar3"></i> Calendario</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('informes.*') ? 'active' : '' }}" href="{{ route('informes.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Informes</a></li>
@if($instId)
<li class="nav-item"><a class="nav-link {{ request()->routeIs('instituciones.show') ? 'active' : '' }}" href="{{ route('instituciones.show', $instId) }}"><i class="bi bi-sliders"></i> Config</a></li>
@else
<li class="nav-item"><a class="nav-link disabled" href="#"><i class="bi bi-sliders"></i> Config</a></li>
@endif
