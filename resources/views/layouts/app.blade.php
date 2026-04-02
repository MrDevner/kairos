<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kairos') — Sistema de Control Horario</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --celeste:    #75AADB;
            --azul:       #1B4F72;
            --azul-light: #2471A3;
            --dorado:     #D4A017;
            --blanco:     #FFFFFF;
            --gris-bg:    #F4F6F8;
            --gris-texto: #495057;
            --gris-borde: #DEE2E6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gris-bg);
            color: var(--gris-texto);
            padding-top: 112px;
        }

        /* ── Header ────────────────────────────────────────────────── */
        #kairos-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            background: var(--celeste);
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
        }
        #kairos-header .brand {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--azul);
            text-decoration: none;
            white-space: nowrap;
        }
        #kairos-header .brand i { font-size: 1.5rem; }
        #kairos-header .institucion-activa {
            flex: 1;
            text-align: center;
            font-size: .9rem;
            font-weight: 600;
            color: var(--azul);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0 1rem;
        }
        #kairos-header .header-acciones {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .btn-notif {
            position: relative;
            background: none;
            border: none;
            color: var(--azul);
            font-size: 1.3rem;
            padding: .25rem;
            cursor: pointer;
            line-height: 1;
        }
        .btn-notif .badge-notif {
            position: absolute;
            top: -2px; right: -4px;
            background: var(--dorado);
            color: #fff;
            font-size: .6rem;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.2;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--azul);
        }
        .user-avatar-placeholder {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--azul);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem;
            flex-shrink: 0;
        }
        .dropdown-toggle-user {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: .4rem;
            color: var(--azul);
            font-weight: 600;
            font-size: .85rem;
            cursor: pointer;
            padding: .25rem .5rem;
            border-radius: .375rem;
        }
        .dropdown-toggle-user:hover { background: rgba(27,79,114,.1); }
        .dropdown-toggle-user::after { display: none; }

        /* ── Navbar ─────────────────────────────────────────────────── */
        #kairos-nav {
            position: fixed;
            top: 56px; left: 0; right: 0;
            height: 56px;
            background: var(--azul);
            z-index: 1029;
            box-shadow: 0 2px 4px rgba(0,0,0,.2);
            overflow: visible;
        }
        #kairos-nav-scroll {
            overflow-x: auto;
            overflow-y: visible;
            height: 56px;
        }
        #kairos-nav .nav { flex-wrap: nowrap; height: 56px; }
        #kairos-nav .nav-link {
            color: rgba(255,255,255,.85) !important;
            font-size: .84rem;
            font-weight: 500;
            padding: 0 1rem !important;
            display: flex;
            align-items: center;
            gap: .35rem;
            height: 56px;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
            transition: color .15s, border-color .15s, background .15s;
        }
        #kairos-nav .nav-link:hover,
        #kairos-nav .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,.1);
            border-bottom-color: var(--dorado);
        }
        #kairos-nav .dropdown-menu {
            background: var(--azul-light);
            border: none;
            border-radius: .375rem;
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
            min-width: 200px;
        }
        #kairos-nav .dropdown-item {
            color: rgba(255,255,255,.9);
            font-size: .84rem;
            padding: .5rem 1rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        #kairos-nav .dropdown-item:hover {
            background: rgba(255,255,255,.15);
            color: #fff;
        }
        #kairos-nav .dropdown-divider { border-color: rgba(255,255,255,.2); }

        /* ── Breadcrumb ─────────────────────────────────────────────── */
        .kairos-breadcrumb {
            background: var(--blanco);
            border-bottom: 1px solid var(--gris-borde);
            padding: .5rem 1.25rem;
            font-size: .82rem;
        }
        .kairos-breadcrumb .breadcrumb { margin: 0; }
        .kairos-breadcrumb .breadcrumb-item a { color: var(--azul-light); }
        .kairos-breadcrumb .breadcrumb-item.active { color: var(--gris-texto); }

        /* ── Contenido ──────────────────────────────────────────────── */
        .kairos-content {
            padding: 1.5rem 1.25rem;
            min-height: calc(100vh - 200px);
        }

        /* ── Cards ──────────────────────────────────────────────────── */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-header {
            background: var(--azul);
            color: #fff;
            font-weight: 600;
        }

        /* ── Footer ─────────────────────────────────────────────────── */
        #kairos-footer {
            background: var(--azul);
            color: rgba(255,255,255,.7);
            text-align: center;
            padding: .75rem 1rem;
            font-size: .8rem;
            margin-top: 2rem;
        }
        #kairos-footer a {
            color: rgba(255,255,255,.85);
            text-decoration: none;
            margin: 0 .5rem;
        }
        #kairos-footer a:hover { color: #fff; text-decoration: underline; }

        @media (max-width: 768px) {
            body { padding-top: 56px; }
            #kairos-nav { display: none; }
            #kairos-header .institucion-activa { display: none; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
<header id="kairos-header">

    <a href="{{ route('home') }}" class="brand">
        <i class="bi bi-compass-fill"></i>
        KAIROS
    </a>

    <span class="institucion-activa">
        @php
            $instActiva = session('institucion_activa_id')
                ? \App\Models\Institucion::find(session('institucion_activa_id'))
                : null;
        @endphp
        {{ $instActiva?->nombre ?? 'Sin institución seleccionada' }}
    </span>

    <div class="header-acciones">
        <button class="btn-notif" title="Notificaciones">
            <i class="bi bi-bell-fill"></i>
            <span class="badge-notif d-none" id="badge-notif">0</span>
        </button>

        <div class="dropdown">
            <button class="dropdown-toggle-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                @if(auth()->user()->foto)
                    <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="" class="user-avatar">
                @else
                    <div class="user-avatar-placeholder">
                        {{ strtoupper(substr(auth()->user()->nombres ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->apellidos ?? '', 0, 1)) }}
                    </div>
                @endif
                <span class="d-none d-md-inline">{{ auth()->user()->apellidos }}</span>
                <i class="bi bi-chevron-down" style="font-size:.7rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle"></i> Perfil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Configuración</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- ══ NAVBAR ══════════════════════════════════════════════════════════════ --}}
<nav id="kairos-nav">
<div id="kairos-nav-scroll">
    <ul class="nav">
        @php $user = auth()->user(); $instId = (int) session('institucion_activa_id', 0); @endphp

        @if($user->hasRole('Administrador General'))
            @include('layouts._nav_admin_general')
        @elseif($user->tieneRolEnInstitucion('Administrador', $instId))
            @include('layouts._nav_admin_institucion')
        @elseif($user->tieneRolEnInstitucion('Jefe de Personal', $instId))
            @include('layouts._nav_jefe_personal')
        @elseif($user->tieneRolEnInstitucion('Departamento Personal', $instId))
            @include('layouts._nav_depto_personal')
        @elseif($user->tieneRolEnInstitucion('Director Administrativo', $instId))
            @include('layouts._nav_director')
        @elseif($user->tieneRolEnInstitucion('Auditor', $instId))
            @include('layouts._nav_director')
        @else
            @include('layouts._nav_usuario')
        @endif
    </ul>
</div>
</nav>

{{-- ══ BREADCRUMB ══════════════════════════════════════════════════════════ --}}
<div class="kairos-breadcrumb">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}"><i class="bi bi-house-fill"></i></a>
            </li>
            @yield('breadcrumb')
        </ol>
    </nav>
</div>

{{-- ══ CONTENIDO ═══════════════════════════════════════════════════════════ --}}
<main class="kairos-content">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-3">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-x-circle-fill me-2"></i>
            <strong>Corrija los siguientes errores:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')

</main>

{{-- ══ FOOTER ══════════════════════════════════════════════════════════════ --}}
<footer id="kairos-footer">
    KAIROS v1.0 — Sistema de Control Horario Institucional &nbsp;|&nbsp;
    <a href="#">Soporte</a>
    <a href="#">Documentación</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    // Dropdowns del nav: strategy fixed para que ignoren el overflow-x del contenedor
    document.querySelectorAll('#kairos-nav .dropdown-toggle').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: { strategy: 'fixed' }
        });
    });

    document.querySelectorAll('.alert.fade.show').forEach(function (el) {
        setTimeout(function () { bootstrap.Alert.getOrCreateInstance(el).close(); }, 6000);
    });
</script>
@stack('scripts')
</body>
</html>
