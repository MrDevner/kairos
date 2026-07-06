@php $user = auth()->user(); @endphp

{{-- Dashboard --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</li>

{{-- Principal --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle"></i> Principal
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item {{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}"><i class="bi bi-person-fill"></i> Perfil</a></li>
        <li><a class="dropdown-item" href="{{ route('perfil') }}#metodos-login"><i class="bi bi-key-fill"></i> Métodos de login</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('informes.*') ? 'active' : '' }}" href="{{ route('informes.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Informes personales</a></li>
    </ul>
</li>

{{-- Personal --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('avisos.*','designaciones.*','ddjj.*','licencias.*','usuarios.*','marcas.*') ? 'active' : '' }}"
       href="#" data-bs-toggle="dropdown">
        <i class="bi bi-people-fill"></i> Personal
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item {{ request()->routeIs('avisos.*') ? 'active' : '' }}" href="{{ route('avisos.index') }}"><i class="bi bi-megaphone-fill"></i> Avisos del personal</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('designaciones.*') ? 'active' : '' }}" href="{{ route('designaciones.index') }}"><i class="bi bi-briefcase-fill"></i> Designaciones</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('ddjj.*') ? 'active' : '' }}" href="{{ route('ddjj.index') }}"><i class="bi bi-file-text-fill"></i> DDJJ</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('licencias.*') ? 'active' : '' }}" href="{{ route('licencias.index') }}"><i class="bi bi-calendar-check-fill"></i> Licencias & Permisos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('marcas.*') ? 'active' : '' }}" href="{{ route('marcas.index') }}"><i class="bi bi-fingerprint"></i> Marcas del personal</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}"><i class="bi bi-person-badge-fill"></i> Usuarios</a></li>
    </ul>
</li>

{{-- Informes --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('informes.*') ? 'active' : '' }}"
       href="#" data-bs-toggle="dropdown">
        <i class="bi bi-bar-chart-fill"></i> Informes
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item {{ request()->routeIs('informes.marcas') ? 'active' : '' }}" href="{{ route('informes.marcas') }}"><i class="bi bi-clock-history"></i> Mensual de marcas</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('informes.index') ? 'active' : '' }}" href="{{ route('informes.index') }}"><i class="bi bi-person-lines-fill"></i> General del usuario</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('informes.resumen-dependencia') ? 'active' : '' }}" href="{{ route('informes.resumen-dependencia') }}"><i class="bi bi-diagram-3-fill"></i> Resumen por dependencia</a></li>
    </ul>
</li>

{{-- Institución --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('dependencias.*','dispositivos.*','cargos.*','roles.*','tipos-licencia.*','calendario.*') ? 'active' : '' }}"
       href="#" data-bs-toggle="dropdown">
        <i class="bi bi-building-fill"></i> Institución
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item {{ request()->routeIs('dependencias.*') ? 'active' : '' }}" href="{{ route('dependencias.index') }}"><i class="bi bi-diagram-3-fill"></i> Dependencias</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('dispositivos.*') ? 'active' : '' }}" href="{{ route('dispositivos.index') }}"><i class="bi bi-hdd-network-fill"></i> Dispositivos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}" href="{{ route('cargos.index') }}"><i class="bi bi-briefcase-fill"></i> Cargos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-fill-check"></i> Roles & permisos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('tipos-licencia.*') ? 'active' : '' }}" href="{{ route('tipos-licencia.index') }}"><i class="bi bi-card-list"></i> Tipos de licencias</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}"><i class="bi bi-calendar-event"></i> Calendario</a></li>
    </ul>
</li>

{{-- Tickets de soporte --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
        <i class="bi bi-life-preserver"></i> Tickets
        @if(($ticketsNoLeidos ?? 0) > 0)
            <span class="badge bg-danger rounded-pill">{{ $ticketsNoLeidos }}</span>
        @endif
    </a>
</li>

{{-- Logs --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}" href="{{ route('logs.index') }}">
        <i class="bi bi-journal-text"></i> Logs
    </a>
</li>

{{-- Administrador (solo Administrador General) --}}
@if($user->permisos()->administrador()->tieneTodosLosPermisos())
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('instituciones.*') ? 'active' : '' }}"
       href="#" data-bs-toggle="dropdown">
        <i class="bi bi-shield-lock-fill"></i> Administrador
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item"
               href="{{ config('kairos.phpmyadmin_url') }}"
               target="_blank" rel="noopener noreferrer">
                <i class="bi bi-database-fill-gear"></i> Acceso a BD
                <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.72rem;opacity:.7"></i>
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item {{ request()->routeIs('usuarios.*') && request()->boolean('todos') ? 'active' : '' }}"
               href="{{ route('usuarios.index', ['todos' => 1]) }}">
                <i class="bi bi-people-fill"></i> Todos los usuarios
            </a>
        </li>
        <li><a class="dropdown-item {{ request()->routeIs('instituciones.*') ? 'active' : '' }}" href="{{ route('instituciones.index') }}"><i class="bi bi-building-fill-gear"></i> Instituciones</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}" href="{{ route('cargos.index') }}"><i class="bi bi-briefcase-fill"></i> Cargos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-fill-check"></i> Roles & permisos</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('tipos-licencia.*') ? 'active' : '' }}" href="{{ route('tipos-licencia.index') }}"><i class="bi bi-card-list"></i> Tipos de licencias</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}"><i class="bi bi-calendar-event"></i> Calendario</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item {{ request()->routeIs('tickets.categorias.*') ? 'active' : '' }}" href="{{ route('tickets.categorias.index') }}"><i class="bi bi-tags-fill"></i> Categorías de tickets</a></li>
        <li><a class="dropdown-item {{ request()->routeIs('admin.errores-servidor.*') ? 'active' : '' }}" href="{{ route('admin.errores-servidor.index') }}"><i class="bi bi-bug-fill"></i> Errores de servidor</a></li>
    </ul>
</li>
@endif
