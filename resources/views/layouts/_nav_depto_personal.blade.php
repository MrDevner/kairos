<li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('avisos.*') ? 'active' : '' }}" href="{{ route('avisos.index') }}"><i class="bi bi-megaphone"></i> Avisos</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-text"></i> DDJJ</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar-check"></i> Licencias</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clock-history"></i> Marcas</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-earmark-bar-graph"></i> Informes</a></li>
