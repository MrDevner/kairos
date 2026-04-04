<li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('instituciones.*') ? 'active' : '' }}" href="{{ route('instituciones.index') }}"><i class="bi bi-building"></i> Instituciones</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}"><i class="bi bi-people"></i> Usuarios</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-check"></i> Roles</a></li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-sliders"></i> Config</a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item {{ request()->routeIs('dispositivos.*') ? 'active' : '' }}" href="{{ route('dispositivos.index') }}"><i class="bi bi-hdd-network"></i> Dispositivos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}"><i class="bi bi-calendar-event"></i> Calendario</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('tipos-licencia.*') ? 'active' : '' }}" href="{{ route('tipos-licencia.index') }}"><i class="bi bi-card-list"></i> Tipos de Licencia</a></li>
    </ul>
</li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('logs.index') }}"><i class="bi bi-journal-text"></i> Logs</a></li>
