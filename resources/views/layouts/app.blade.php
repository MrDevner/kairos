<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kairos') — Sistema de Control Horario</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* ── Paleta institucional Kairos (Opción 1: Azul + Verde-agua) ── */
            --bs-primary:      #2E5EAA;
            --bs-primary-rgb:  46, 94, 170;
            --kairos-primary-light: #E8F1FC;
            --bs-secondary:     #4CAF93;
            --bs-secondary-rgb: 76, 175, 147;
            --kairos-accent:     #F2A65A;
            --kairos-accent-rgb: 242, 166, 90;
            --bs-light:    #F7F9FC;
            --bs-light-rgb: 247, 249, 252;
            --kairos-bg:   #F7F9FC;
            --bs-dark:     #2B2D42;
            --bs-dark-rgb: 43, 45, 66;
            --kairos-text: #2B2D42;
            --bs-danger:     #DC3545;
            --bs-danger-rgb: 220, 53, 69;
            --bs-warning:     var(--kairos-accent);
            --bs-warning-rgb: var(--kairos-accent-rgb);
            --bs-success:     var(--bs-secondary);
            --bs-success-rgb: var(--bs-secondary-rgb);
            --bs-body-bg:    var(--kairos-bg);
            --bs-body-color: var(--kairos-text);
            --bs-border-color: #DDE3EC;
            --bs-link-color:       var(--bs-primary);
            --bs-link-hover-color: #26518F;

            --kairos-shadow-sm: 0 2px 8px rgba(46, 94, 170, .08);
            --kairos-shadow-md: 0 8px 24px rgba(46, 94, 170, .14);
            --kairos-shadow-lg: 0 16px 40px rgba(43, 45, 66, .16);
            --kairos-radius:    .75rem;
            --kairos-radius-sm: .5rem;
            --kairos-radius-lg: 1rem;
            --kairos-gradient: linear-gradient(135deg, var(--bs-primary) 0%, #26518F 100%);
            --kairos-gradient-soft: linear-gradient(135deg, rgba(46,94,170,.08) 0%, rgba(76,175,147,.08) 100%);

            /* Alias retro-compatibles: usados inline en ~80 vistas existentes */
            --celeste:    #86AEE0;
            --azul:       var(--bs-primary);
            --azul-light: #3B6BC2;
            --dorado:     var(--kairos-accent);
            --blanco:     #FFFFFF;
            --gris-bg:    var(--kairos-bg);
            --gris-texto: var(--kairos-text);
            --gris-borde: var(--bs-border-color);
        }

        * { scrollbar-width: thin; scrollbar-color: rgba(43,45,66,.2) transparent; }
        ::-webkit-scrollbar { height: 6px; width: 6px; }
        ::-webkit-scrollbar-thumb { background: rgba(43,45,66,.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(43,45,66,.35); }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gris-bg);
            color: var(--gris-texto);
            padding-top: 112px;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Header ────────────────────────────────────────────────── */
        #kairos-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(10px) saturate(160%);
            -webkit-backdrop-filter: blur(10px) saturate(160%);
            border-bottom: 1px solid rgba(46,94,170,.08);
            z-index: 1030;
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 0 1.25rem;
            box-shadow: 0 1px 0 rgba(46,94,170,.04);
        }
        .btn-mobile-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px; height: 38px;
            border: none;
            background: var(--kairos-primary-light);
            color: var(--azul);
            border-radius: var(--kairos-radius-sm);
            font-size: 1.15rem;
            flex-shrink: 0;
            transition: background .15s;
        }
        .btn-mobile-toggle:hover { background: rgba(var(--bs-primary-rgb), .16); }
        #kairos-header .brand {
            display: flex;
            align-items: center;
            gap: .55rem;
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: .02em;
            color: var(--kairos-text);
            text-decoration: none;
            white-space: nowrap;
        }
        #kairos-header .brand .brand-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px; height: 34px;
            border-radius: 10px;
            background: var(--kairos-gradient);
            color: #fff;
            font-size: 1.1rem;
            box-shadow: var(--kairos-shadow-sm);
            flex-shrink: 0;
        }
        #kairos-header .brand .brand-text { color: var(--azul); }
        #kairos-header .institucion-activa {
            flex: 1;
            text-align: center;
            font-size: .88rem;
            font-weight: 600;
            color: var(--azul);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0 1rem;
        }
        #kairos-header .institucion-activa-dropdown {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-toggle {
            color: var(--azul) !important;
            background: var(--kairos-primary-light);
            font-size: .85rem;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: .4rem .9rem;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: background .15s, border-color .15s;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-toggle:hover {
            background: #fff;
            border-color: rgba(var(--bs-primary-rgb),.25);
        }
        #kairos-header .institucion-activa-dropdown .dropdown-toggle::after {
            color: var(--azul);
            margin-left: .5rem;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-menu {
            background: #fff;
            border: 1px solid var(--gris-borde);
            border-radius: var(--kairos-radius);
            box-shadow: var(--kairos-shadow-lg);
            padding: .4rem;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-item {
            color: var(--gris-texto);
            font-size: .85rem;
            border-radius: var(--kairos-radius-sm);
            padding: .5rem .75rem;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-item:hover {
            background: var(--kairos-primary-light);
        }
        #kairos-header .header-acciones {
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-shrink: 0;
        }
        .btn-notif {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px; height: 38px;
            background: transparent;
            border: none;
            color: var(--azul);
            font-size: 1.15rem;
            border-radius: 50%;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-notif:hover { background: var(--kairos-primary-light); }
        .btn-notif .badge-notif {
            position: absolute;
            top: 2px; right: 2px;
            background: var(--dorado);
            color: var(--kairos-text);
            font-size: .6rem;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.2;
            font-weight: 700;
            box-shadow: 0 0 0 2px #fff;
        }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb),.35);
        }
        .user-avatar-placeholder {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--kairos-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .8rem;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb),.2);
        }
        .dropdown-toggle-user {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: var(--kairos-text);
            font-weight: 600;
            font-size: .85rem;
            cursor: pointer;
            padding: .3rem .6rem .3rem .3rem;
            border-radius: 999px;
            transition: background .15s;
        }
        .dropdown-toggle-user:hover { background: var(--kairos-primary-light); }
        .dropdown-toggle-user::after { display: none; }

        /* ── Navbar ─────────────────────────────────────────────────── */
        #kairos-nav {
            position: fixed;
            top: 60px; left: 0; right: 0;
            height: 50px;
            background: #fff;
            border-bottom: 1px solid var(--gris-borde);
            z-index: 1029;
            overflow: visible;
        }
        #kairos-nav-scroll {
            overflow-x: auto;
            overflow-y: visible;
            height: 50px;
        }
        #kairos-nav .nav { flex-wrap: nowrap; height: 50px; padding: 0 .5rem; }
        #kairos-nav .nav-link {
            color: #6B7280 !important;
            font-size: .84rem;
            font-weight: 600;
            padding: 0 .9rem !important;
            margin: 0 .1rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            height: 50px;
            white-space: nowrap;
            position: relative;
            border-radius: 0;
            transition: color .15s;
        }
        #kairos-nav .nav-link i { font-size: .95rem; opacity: .85; }
        #kairos-nav .nav-link::after {
            content: '';
            position: absolute;
            left: .5rem; right: .5rem; bottom: 0;
            height: 2.5px;
            border-radius: 2px 2px 0 0;
            background: transparent;
            transition: background .15s;
        }
        #kairos-nav .nav-link:hover {
            color: var(--azul) !important;
            background: rgba(var(--bs-primary-rgb),.05);
        }
        #kairos-nav .nav-link.active {
            color: var(--azul) !important;
        }
        #kairos-nav .nav-link.active::after,
        #kairos-nav .nav-link.show::after {
            background: var(--azul);
        }
        #kairos-nav .dropdown-menu {
            background: #fff;
            border: 1px solid var(--gris-borde);
            border-radius: var(--kairos-radius);
            box-shadow: var(--kairos-shadow-lg);
            min-width: 220px;
            padding: .4rem;
            margin-top: .35rem !important;
            animation: kairosFadeIn .12s ease-out;
        }
        #kairos-nav .dropdown-item {
            color: var(--gris-texto);
            font-size: .84rem;
            font-weight: 500;
            padding: .55rem .75rem;
            border-radius: var(--kairos-radius-sm);
            display: flex;
            align-items: center;
            gap: .55rem;
        }
        #kairos-nav .dropdown-item i { color: var(--azul); font-size: .95rem; width: 1.1em; text-align: center; }
        #kairos-nav .dropdown-item:hover {
            background: var(--kairos-primary-light);
            color: var(--azul);
        }
        #kairos-nav .dropdown-item.active {
            background: var(--kairos-primary-light);
            color: var(--azul);
            font-weight: 700;
        }
        #kairos-nav .dropdown-divider { border-color: var(--gris-borde); margin: .35rem 0; }
        #kairos-nav .nav-item .badge { font-size: .65rem; }

        @keyframes kairosFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Offcanvas (menú móvil) ────────────────────────────────── */
        .kairos-offcanvas { width: 280px; }
        .kairos-offcanvas .offcanvas-header {
            background: var(--kairos-gradient);
            color: #fff;
        }
        .kairos-offcanvas .offcanvas-header .brand-text { color: #fff; }
        .kairos-offcanvas .offcanvas-header .btn-close { filter: brightness(0) invert(1); }
        .kairos-mobile-nav .nav-link {
            color: var(--gris-texto) !important;
            padding: .8rem 1.1rem !important;
            border-bottom: 1px solid var(--gris-borde);
            font-weight: 600;
            font-size: .9rem;
        }
        .kairos-mobile-nav .nav-link.active { color: var(--azul) !important; background: var(--kairos-primary-light); }
        .kairos-mobile-nav .dropdown-menu {
            position: static !important;
            width: 100%;
            box-shadow: none;
            border: none;
            border-radius: 0;
            background: #FAFBFD;
            margin: 0 !important;
            animation: none;
        }
        .kairos-mobile-nav .badge { margin-left: auto; }

        /* ── Breadcrumb ─────────────────────────────────────────────── */
        .kairos-breadcrumb {
            background: transparent;
            padding: .9rem 1.25rem .25rem;
            font-size: .82rem;
        }
        .kairos-breadcrumb .breadcrumb {
            margin: 0;
            background: #fff;
            display: inline-flex;
            padding: .4rem .9rem;
            border-radius: 999px;
            border: 1px solid var(--gris-borde);
            box-shadow: var(--kairos-shadow-sm);
        }
        .kairos-breadcrumb .breadcrumb-item a { color: var(--azul-light); font-weight: 500; }
        .kairos-breadcrumb .breadcrumb-item.active { color: var(--gris-texto); font-weight: 600; }

        /* ── Contenido ──────────────────────────────────────────────── */
        .kairos-content {
            padding: 1rem 1.25rem 2rem;
            min-height: calc(100vh - 220px);
            animation: kairosContentIn .25s ease-out;
        }
        @keyframes kairosContentIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Cards ──────────────────────────────────────────────────── */
        .card {
            border: 1px solid var(--gris-borde);
            border-radius: var(--kairos-radius);
            box-shadow: var(--kairos-shadow-sm);
        }
        .card-header {
            background: var(--kairos-gradient);
            color: #fff;
            font-weight: 700;
            letter-spacing: .01em;
            border: none;
            border-radius: var(--kairos-radius) var(--kairos-radius) 0 0 !important;
            padding: .85rem 1.1rem;
        }

        /* ── Botones ────────────────────────────────────────────────── */
        .btn {
            border-radius: var(--kairos-radius-sm);
            font-weight: 600;
            transition: transform .12s ease, box-shadow .12s ease, background-color .15s, border-color .15s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-primary {
            --bs-btn-bg: var(--bs-primary);
            --bs-btn-border-color: var(--bs-primary);
            --bs-btn-hover-bg: #26518F;
            --bs-btn-hover-border-color: #26518F;
            --bs-btn-active-bg: #204370;
            --bs-btn-active-border-color: #204370;
            --bs-btn-focus-shadow-rgb: var(--bs-primary-rgb);
            box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), .25);
        }
        .btn-outline-primary {
            --bs-btn-color: var(--bs-primary);
            --bs-btn-border-color: var(--bs-primary);
            --bs-btn-hover-bg: var(--bs-primary);
            --bs-btn-hover-border-color: var(--bs-primary);
            --bs-btn-active-bg: var(--bs-primary);
            --bs-btn-active-border-color: var(--bs-primary);
            --bs-btn-focus-shadow-rgb: var(--bs-primary-rgb);
        }
        .btn-secondary {
            --bs-btn-bg: var(--bs-secondary);
            --bs-btn-border-color: var(--bs-secondary);
            --bs-btn-hover-bg: #41957D;
            --bs-btn-hover-border-color: #41957D;
            --bs-btn-active-bg: #357E69;
            --bs-btn-active-border-color: #357E69;
            --bs-btn-focus-shadow-rgb: var(--bs-secondary-rgb);
        }
        .btn-outline-secondary {
            --bs-btn-color: var(--bs-secondary);
            --bs-btn-border-color: var(--bs-secondary);
            --bs-btn-hover-bg: var(--bs-secondary);
            --bs-btn-hover-border-color: var(--bs-secondary);
            --bs-btn-focus-shadow-rgb: var(--bs-secondary-rgb);
        }
        .btn-warning {
            --bs-btn-bg: var(--kairos-accent);
            --bs-btn-border-color: var(--kairos-accent);
            --bs-btn-color: var(--kairos-text);
            --bs-btn-hover-bg: #CE8D4D;
            --bs-btn-hover-border-color: #CE8D4D;
            --bs-btn-hover-color: #fff;
        }

        /* ── Formularios ────────────────────────────────────────────── */
        .form-control, .form-select {
            border-color: var(--gris-borde);
            border-radius: var(--kairos-radius-sm);
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:hover, .form-select:hover { border-color: rgba(var(--bs-primary-rgb),.4); }
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .15);
        }
        .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .form-check-input:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), .25);
        }

        /* ── Paginación ─────────────────────────────────────────────── */
        .pagination {
            --bs-pagination-active-bg: var(--bs-primary);
            --bs-pagination-active-border-color: var(--bs-primary);
            --bs-pagination-focus-box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), .25);
        }
        .pagination .page-link { border-radius: var(--kairos-radius-sm); margin: 0 .12rem; border-color: var(--gris-borde); }

        /* ── Alerts ─────────────────────────────────────────────────── */
        .alert { border-radius: var(--kairos-radius); border-width: 1px; }
        .alert-success { background-color: #E3F5EF; border-color: #B9E4D6; color: #1F6E58; }
        .alert-warning { background-color: #FDF0E1; border-color: #F5CB9B; color: #8A5A22; }
        .alert-danger  { background-color: #FBE4E7; border-color: #F2AEB6; color: #842029; }

        /* ── Footer ─────────────────────────────────────────────────── */
        #kairos-footer {
            background: #fff;
            border-top: 1px solid var(--gris-borde);
            color: #8A93A6;
            text-align: center;
            padding: 1rem 1rem;
            font-size: .8rem;
            margin-top: 2rem;
        }
        #kairos-footer a {
            color: var(--azul-light);
            text-decoration: none;
            margin: 0 .5rem;
            font-weight: 500;
        }
        #kairos-footer a:hover { color: var(--azul); text-decoration: underline; }

        @media (max-width: 767.98px) {
            body { padding-top: 60px; }
            #kairos-nav { display: none; }
            #kairos-header .institucion-activa,
            #kairos-header .institucion-activa-dropdown { display: none; }
            .btn-mobile-toggle { display: inline-flex; }
            .kairos-content { padding: .85rem .9rem 1.5rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
<header id="kairos-header">

    <button class="btn-mobile-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#kairosMobileNav" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
    </button>

    <a href="{{ route('home') }}" class="brand">
        <span class="brand-badge"><i class="bi bi-compass-fill"></i></span>
        <span class="brand-text">KAIROS</span>
    </a>

    @php
        $instActiva  = session('institucion_activa_id')
            ? \App\Models\Institucion::find(session('institucion_activa_id'))
            : null;
        $authUser    = auth()->user();
        $soloIds     = $authUser->permisos()->administrador()->tieneTodosLosPermisos()
            ? null
            : $authUser->rolesInstitucion()->vigente()->pluck('id_institucion')->map(fn($v) => (int)$v)->toArray();
        $listaInst   = \App\Models\Institucion::listaJerarquica($soloIds);
    @endphp

    @if(count($listaInst) > 1)
        <div class="dropdown institucion-activa-dropdown">
            <button class="btn dropdown-toggle text-decoration-none"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-building me-1"></i>
                {{ $instActiva?->nombre ?? 'Seleccionar institución' }}
            </button>
            <ul class="dropdown-menu shadow" style="min-width:280px;max-height:400px;overflow-y:auto">
                <li><h6 class="dropdown-header small">Cambiar institución</h6></li>
                @foreach($listaInst as $item)
                    @php $inst = $item['institucion']; $nivel = $item['nivel']; @endphp
                    <li>
                        <form method="POST" action="{{ route('institucion-activa.cambiar') }}">
                            @csrf
                            <input type="hidden" name="id_institucion" value="{{ $inst->id }}">
                            <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-1 {{ $instActiva?->id === $inst->id ? 'fw-bold' : '' }}"
                                    style="padding-left: {{ 1 + $nivel * 1.25 }}rem">
                                @if($instActiva?->id === $inst->id)
                                    <i class="bi bi-check2 text-success flex-shrink-0"></i>
                                @elseif($nivel === 0)
                                    <i class="bi bi-building text-muted flex-shrink-0"></i>
                                @else
                                    <i class="bi bi-diagram-2 text-muted flex-shrink-0" style="font-size:.75rem"></i>
                                @endif
                                {{ $inst->nombre }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <span class="institucion-activa">
            <i class="bi bi-building me-1"></i>
            {{ $instActiva?->nombre ?? ($listaInst[0]['institucion']->nombre ?? 'Sin institución') }}
        </span>
        @if(count($listaInst) === 1 && !$instActiva)
            <form method="POST" action="{{ route('institucion-activa.cambiar') }}" id="form-inst-auto" class="d-none">
                @csrf
                <input type="hidden" name="id_institucion" value="{{ $listaInst[0]['institucion']->id }}">
            </form>
            <script>document.getElementById('form-inst-auto').submit();</script>
        @endif
    @endif

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
                <li><a class="dropdown-item {{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}"><i class="bi bi-person-circle"></i> Mi perfil</a></li>
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

{{-- ══ NAVBAR (escritorio) ═════════════════════════════════════════════════ --}}
<nav id="kairos-nav">
<div id="kairos-nav-scroll">
    <ul class="nav">
        @include('layouts._nav')
    </ul>
</div>
</nav>

{{-- ══ MENÚ MÓVIL (offcanvas) ══════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start kairos-offcanvas" tabindex="-1" id="kairosMobileNav" aria-labelledby="kairosMobileNavLabel">
    <div class="offcanvas-header">
        <a href="{{ route('home') }}" class="brand" id="kairosMobileNavLabel">
            <span class="brand-badge"><i class="bi bi-compass-fill"></i></span>
            <span class="brand-text">KAIROS</span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column kairos-mobile-nav">
            @include('layouts._nav')
        </ul>
    </div>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/**
 * Inicializa un <select> como Select2 con búsqueda AJAX de usuarios.
 * @param {string|Element} selector  – selector CSS o elemento DOM
 * @param {object}         opts      – opciones extra (dropdownParent, etc.)
 */
function initSelect2Usuario(selector, opts) {
    opts = opts || {};
    $(selector).select2($.extend({
        theme:              'bootstrap-5',
        width:              '100%',
        placeholder:        'Buscar por apellido, nombre o documento…',
        allowClear:         true,
        minimumInputLength: 3,
        language: {
            inputTooShort:  function () { return 'Ingrese al menos 3 caracteres'; },
            searching:      function () { return 'Buscando…'; },
            noResults:      function () { return 'Sin resultados'; },
            errorLoading:   function () { return 'Error al cargar'; },
        },
        ajax: {
            url:     '{{ route("usuarios.buscar") }}',
            dataType:'json',
            delay:   300,
            data:    function (p) { return { q: p.term }; },
            processResults: function (d) { return { results: d.results }; },
            cache:   true,
        },
    }, opts));
}
</script>
<script>
    // Dropdowns del nav de escritorio: strategy fixed para que ignoren el overflow-x del contenedor
    document.querySelectorAll('#kairos-nav .dropdown-toggle').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: { strategy: 'fixed' }
        });
    });

    document.querySelectorAll('.alert.fade.show').forEach(function (el) {
        setTimeout(function () { bootstrap.Alert.getOrCreateInstance(el).close(); }, 6000);
    });

    // Cierra el offcanvas móvil al navegar a un enlace del menú
    document.querySelectorAll('#kairosMobileNav a.dropdown-item, #kairosMobileNav > .offcanvas-body > .nav > li > a.nav-link:not(.dropdown-toggle)').forEach(function (el) {
        el.addEventListener('click', function () {
            var oc = bootstrap.Offcanvas.getInstance(document.getElementById('kairosMobileNav'));
            if (oc) oc.hide();
        });
    });
</script>
@stack('scripts')
</body>
</html>
