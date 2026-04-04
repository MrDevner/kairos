<li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-diagram-3"></i> Dependencias</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-people"></i> Personal</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-briefcase"></i> Designaciones</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}"><i class="bi bi-calendar3"></i> Calendario</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-earmark-bar-graph"></i> Informes</a></li>
<li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-sliders"></i> Config</a></li>
