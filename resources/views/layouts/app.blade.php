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
        #kairos-header .institucion-activa-dropdown {
            flex: 1;
            text-align: center;
            padding: 0 .5rem;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-toggle {
            color: var(--azul) !important;
            font-size: .9rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-toggle::after {
            color: var(--azul);
        }
        #kairos-header .institucion-activa-dropdown .dropdown-menu {
            background: #fff;
            border: 1px solid var(--gris-borde);
        }
        #kairos-header .institucion-activa-dropdown .dropdown-item {
            color: var(--gris-texto);
            font-size: .85rem;
        }
        #kairos-header .institucion-activa-dropdown .dropdown-item:hover {
            background: rgba(27,79,114,.08);
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

    @php
        $instActiva  = session('institucion_activa_id')
            ? \App\Models\Institucion::find(session('institucion_activa_id'))
            : null;
        $authUser    = auth()->user();
        $soloIds     = $authUser->hasRole('Administrador General')
            ? null
            : $authUser->rolesInstitucion()->vigente()->pluck('id_institucion')->map(fn($v) => (int)$v)->toArray();
        $listaInst   = \App\Models\Institucion::listaJerarquica($soloIds);
    @endphp

    @if(count($listaInst) > 1)
        <div class="dropdown institucion-activa-dropdown" style="flex:1;text-align:center;">
            <button class="btn btn-link dropdown-toggle text-decoration-none fw-semibold"
                    style="color:var(--azul);font-size:.9rem;padding:.25rem .5rem"
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

{{-- ══ NAVBAR ══════════════════════════════════════════════════════════════ --}}
<nav id="kairos-nav">
<div id="kairos-nav-scroll">
    <ul class="nav">
        @include('layouts._nav')
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
