<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · Kairos</title>

    {{-- Tema persistido: se aplica antes del primer render para evitar parpadeo --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('k2-theme') === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════════════
           KAIROS · Layout app2 — tokens de diseño
           Paleta institucional: azul #2E5EAA · verde-agua #4CAF93
           · acento #F2A65A · texto #2B2D42 · fondo #F7F9FC
           ═══════════════════════════════════════════════════════════════ */
        :root {
            --k2-primary:        #2E5EAA;
            --k2-primary-rgb:    46, 94, 170;
            --k2-primary-600:    #26518F;
            --k2-primary-700:    #1F4276;
            --k2-primary-50:     #EAF1FB;
            --k2-primary-100:    #D5E3F6;
            --k2-primary-text:   #2E5EAA;

            --k2-teal:           #4CAF93;
            --k2-teal-rgb:       76, 175, 147;
            --k2-teal-50:        #E4F5EE;
            --k2-teal-text:      #1F6E58;

            --k2-amber:          #F2A65A;
            --k2-amber-rgb:      242, 166, 90;
            --k2-amber-50:       #FDF0E1;
            --k2-amber-text:     #8A5A22;

            --k2-danger:         #D64550;
            --k2-danger-rgb:     214, 69, 80;
            --k2-danger-50:      #FCE8EA;
            --k2-danger-text:    #98232C;

            --k2-ink:            #2B2D42;
            --k2-ink-2:          #5C6379;
            --k2-ink-3:          #8B93A7;

            --k2-bg:             #F4F6FA;
            --k2-surface:        #FFFFFF;
            --k2-surface-2:      #F8FAFD;
            --k2-border:         #E2E7F0;
            --k2-border-strong:  #CBD3E1;

            --k2-radius:         14px;
            --k2-radius-sm:      9px;
            --k2-radius-xs:      6px;

            --k2-shadow-xs:      0 1px 2px rgba(43, 45, 66, .05);
            --k2-shadow-sm:      0 1px 3px rgba(43, 45, 66, .06), 0 4px 14px rgba(46, 94, 170, .06);
            --k2-shadow-lg:      0 12px 40px rgba(43, 45, 66, .16);

            --k2-font:           'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --k2-font-display:   'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;

            --k2-topbar-h:       64px;
            --k2-container:      1480px;
        }

        html[data-theme="dark"] {
            --k2-primary-text:   #8FB3EA;
            --k2-primary-50:     rgba(46, 94, 170, .22);
            --k2-primary-100:    rgba(46, 94, 170, .34);
            --k2-teal-50:        rgba(76, 175, 147, .18);
            --k2-teal-text:      #7ED4BA;
            --k2-amber-50:       rgba(242, 166, 90, .18);
            --k2-amber-text:     #F5C289;
            --k2-danger-50:      rgba(214, 69, 80, .2);
            --k2-danger-text:    #F0919A;

            --k2-ink:            #E7EBF3;
            --k2-ink-2:          #A9B1C3;
            --k2-ink-3:          #7E879B;

            --k2-bg:             #0F1320;
            --k2-surface:        #171C2B;
            --k2-surface-2:      #1D2334;
            --k2-border:         #28304A;
            --k2-border-strong:  #374060;

            --k2-shadow-xs:      0 1px 2px rgba(0, 0, 0, .3);
            --k2-shadow-sm:      0 1px 3px rgba(0, 0, 0, .3), 0 4px 14px rgba(0, 0, 0, .25);
            --k2-shadow-lg:      0 12px 40px rgba(0, 0, 0, .5);
        }

        /* ── Base ───────────────────────────────────────────────────── */
        * { scrollbar-width: thin; scrollbar-color: var(--k2-border-strong) transparent; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--k2-border-strong); border-radius: 10px; }

        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: var(--k2-font);
            font-size: .9rem;
            line-height: 1.5;
            color: var(--k2-ink);
            background: var(--k2-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { color: var(--k2-primary-text); }

        /* Nota: no animar `transform` aquí. Popper posiciona los dropdowns con transform
           (strategy fixed) y una animación sobre esa propiedad los hace aparecer en (0,0)
           antes de acomodarse. Se usa la propiedad independiente `translate`. */
        @keyframes k2Pop  { from { opacity: 0; translate: 0 -6px; } to { opacity: 1; translate: 0 0; } }
        @keyframes k2Rise { from { opacity: 0; transform: translateY(8px); }             to { opacity: 1; transform: none; } }

        /* ── Barra superior ─────────────────────────────────────────── */
        .k2-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            height: var(--k2-topbar-h);
            background: var(--k2-surface);
            background: color-mix(in srgb, var(--k2-surface) 88%, transparent);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            backdrop-filter: blur(12px) saturate(160%);
            border-bottom: 1px solid var(--k2-border);
        }
        .k2-topbar::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            background: linear-gradient(90deg, var(--k2-primary) 0%, var(--k2-teal) 62%, var(--k2-amber) 100%);
        }
        .k2-topbar-inner {
            max-width: var(--k2-container);
            height: 100%;
            margin: 0 auto;
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            gap: .9rem;
        }

        .k2-menu-toggle { display: none; }

        .k2-brand {
            display: flex;
            align-items: center;
            gap: .65rem;
            text-decoration: none;
            color: var(--k2-ink);
            flex-shrink: 0;
        }
        .k2-brand:hover { color: var(--k2-ink); }
        .k2-brand-mark {
            width: 38px; height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--k2-primary) 0%, var(--k2-primary-700) 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-family: var(--k2-font-display);
            font-weight: 800;
            font-size: 1.15rem;
            box-shadow: 0 4px 12px rgba(var(--k2-primary-rgb), .3);
        }
        .k2-brand-name {
            display: block;
            font-family: var(--k2-font-display);
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: .06em;
            line-height: 1;
        }
        .k2-brand-sub {
            display: block;
            margin-top: 3px;
            font-size: .64rem;
            font-weight: 600;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
        }

        /* ── Navegación horizontal ──────────────────────────────────── */
        .k2-navwrap { min-width: 0; flex: 0 1 auto; }
        .k2-nav {
            display: flex;
            align-items: center;
            gap: .15rem;
            list-style: none;
            margin: 0 0 0 .4rem;
            padding: 0;
            overflow-x: auto;          /* red de seguridad: nunca desborda la página */
            scrollbar-width: none;
        }
        .k2-nav::-webkit-scrollbar { display: none; }
        .k2-nav-link {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem .8rem;
            border-radius: 10px;
            color: var(--k2-ink-2);
            font-weight: 600;
            font-size: .86rem;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, color .15s, box-shadow .15s;
        }
        .k2-nav-link > .bi:first-child { font-size: 1rem; opacity: .9; }
        .k2-nav-link .k2-caret { font-size: .6rem; opacity: .6; transition: transform .15s; }
        .k2-nav-link.dropdown-toggle::after { display: none; }
        .k2-nav-link:hover,
        .k2-nav-link.show { color: var(--k2-primary-text); background: var(--k2-primary-50); }
        .k2-nav-link.active {
            color: var(--k2-primary-text);
            background: var(--k2-primary-50);
            box-shadow: inset 0 0 0 1px var(--k2-primary-100);
        }
        .k2-nav-link.show .k2-caret { transform: rotate(180deg); }

        /* ── Dropdowns ──────────────────────────────────────────────── */
        .k2-dropdown {
            --bs-dropdown-bg:            var(--k2-surface);
            --bs-dropdown-border-color:  var(--k2-border);
            --bs-dropdown-border-radius: var(--k2-radius);
            --bs-dropdown-padding-x:     .5rem;
            --bs-dropdown-padding-y:     .5rem;
            --bs-dropdown-min-width:     270px;
            --bs-dropdown-spacer:        .55rem;
            --bs-dropdown-color:         var(--k2-ink);
            box-shadow: var(--k2-shadow-lg);
            animation: k2Pop .16s ease-out;
        }
        .k2-dropdown-wide { --bs-dropdown-min-width: 580px; }
        .k2-dropdown-head {
            padding: .35rem .75rem .5rem;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
        }
        .k2-dropdown-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .15rem; }
        .k2-dropdown-item {
            display: flex;
            align-items: center;
            gap: .7rem;
            width: 100%;
            padding: .5rem .65rem;
            border: 0;
            border-radius: var(--k2-radius-sm);
            background: transparent;
            color: var(--k2-ink);
            text-align: left;
            text-decoration: none;
            transition: background .12s;
        }
        .k2-dropdown-item:hover { background: var(--k2-primary-50); color: var(--k2-ink); }
        .k2-dropdown-item.active { background: var(--k2-primary-50); }
        .k2-dropdown-item.active .k2-dropdown-label { color: var(--k2-primary-text); }
        .k2-dropdown-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: var(--k2-primary-50);
            color: var(--k2-primary-text);
            font-size: 1rem;
        }
        .k2-dropdown-item.danger .k2-dropdown-icon { background: var(--k2-danger-50); color: var(--k2-danger-text); }
        .k2-dropdown-text { min-width: 0; }
        .k2-dropdown-label { display: block; font-weight: 600; font-size: .84rem; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .k2-dropdown-desc  { display: block; margin-top: 2px; font-size: .71rem; color: var(--k2-ink-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .k2-dropdown-divider { height: 1px; margin: .4rem .25rem; background: var(--k2-border); }
        .k2-dropdown-ext { margin-left: auto; font-size: .7rem; color: var(--k2-ink-3); }

        /* ── Acciones de la barra ───────────────────────────────────── */
        .k2-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: .35rem;
            flex-shrink: 0;
        }
        .k2-iconbtn {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 11px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--k2-ink-2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .k2-iconbtn:hover, .k2-iconbtn.show { background: var(--k2-primary-50); color: var(--k2-primary-text); }
        .k2-iconbtn.dropdown-toggle::after { display: none; }
        .k2-dot {
            position: absolute;
            top: 7px; right: 7px;
            min-width: 17px; height: 17px;
            padding: 0 4px;
            border-radius: 9px;
            background: var(--k2-danger);
            color: #fff;
            font-size: .62rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 2px var(--k2-surface);
        }

        .k2-search {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            height: 40px;
            min-width: 230px;
            padding: 0 .55rem 0 .85rem;
            border-radius: 11px;
            border: 1px solid var(--k2-border);
            background: var(--k2-surface-2);
            color: var(--k2-ink-3);
            font-size: .82rem;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .k2-search:hover { border-color: var(--k2-border-strong); }
        .k2-search:focus-visible { outline: none; border-color: var(--k2-primary); box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .15); }
        .k2-kbd {
            margin-left: auto;
            padding: 1px 6px;
            border: 1px solid var(--k2-border-strong);
            border-bottom-width: 2px;
            border-radius: 6px;
            background: var(--k2-surface);
            color: var(--k2-ink-2);
            font-size: .64rem;
            font-weight: 600;
            font-family: var(--k2-font);
        }

        .k2-inst {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            height: 40px;
            max-width: 300px;
            padding: 0 .9rem;
            border-radius: 11px;
            border: 1px solid var(--k2-border);
            background: var(--k2-surface);
            color: var(--k2-ink);
            font-weight: 600;
            font-size: .82rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .k2-inst:hover, .k2-inst.show { border-color: var(--k2-primary-100); background: var(--k2-primary-50); color: var(--k2-primary-text); }
        .k2-inst.dropdown-toggle::after { display: none; }
        .k2-inst .bi-building { color: var(--k2-primary-text); }
        .k2-inst .k2-inst-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .k2-inst .k2-caret { font-size: .6rem; opacity: .6; }
        .k2-inst-list { max-height: 400px; overflow-y: auto; }

        .k2-user {
            display: flex;
            align-items: center;
            gap: .6rem;
            height: 44px;
            padding: 0 .55rem 0 .3rem;
            border-radius: 12px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--k2-ink);
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .k2-user:hover, .k2-user.show { border-color: var(--k2-border); background: var(--k2-surface); }
        .k2-user.dropdown-toggle::after { display: none; }
        .k2-user-text { text-align: left; line-height: 1.1; }
        .k2-user-name { display: block; font-size: .82rem; font-weight: 600; }
        .k2-user-role { display: block; margin-top: 2px; font-size: .66rem; color: var(--k2-ink-3); }
        .k2-avatar {
            width: 34px; height: 34px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .k2-avatar-ph {
            width: 34px; height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--k2-primary) 0%, var(--k2-teal) 100%);
            color: #fff;
            font-weight: 700;
            font-size: .78rem;
        }

        /* ── Notificaciones ─────────────────────────────────────────── */
        .k2-notif { --bs-dropdown-min-width: 320px; }
        .k2-notif-item { display: flex; align-items: center; gap: .75rem; padding: .6rem .65rem; border-radius: var(--k2-radius-sm); text-decoration: none; color: var(--k2-ink); transition: background .12s; }
        .k2-notif-item:hover { background: var(--k2-surface-2); color: var(--k2-ink); }
        .k2-notif-count { margin-left: auto; font-family: var(--k2-font-display); font-weight: 800; font-size: 1.05rem; font-variant-numeric: tabular-nums; }
        .k2-notif-item.muted { opacity: .55; }

        /* ── Encabezado de página ───────────────────────────────────── */
        .k2-pagehead {
            max-width: var(--k2-container);
            margin: 0 auto;
            padding: 1.6rem 1.25rem .5rem;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .k2-crumbs {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin: 0 0 .45rem;
            padding: 0;
            list-style: none;
            font-size: .75rem;
            color: var(--k2-ink-3);
        }
        .k2-crumbs a { color: var(--k2-ink-3); text-decoration: none; }
        .k2-crumbs a:hover { color: var(--k2-primary-text); }
        .k2-crumbs li + li::before { content: '/'; margin-right: .4rem; opacity: .5; }
        .k2-crumbs li.active { color: var(--k2-ink-2); font-weight: 600; }
        .k2-title {
            margin: 0;
            font-family: var(--k2-font-display);
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -.01em;
            color: var(--k2-ink);
        }
        .k2-subtitle { margin: .25rem 0 0; font-size: .86rem; color: var(--k2-ink-2); }
        .k2-page-actions { display: flex; gap: .5rem; flex-wrap: wrap; }

        /* ── Contenido ──────────────────────────────────────────────── */
        .k2-main {
            max-width: var(--k2-container);
            margin: 0 auto;
            padding: .75rem 1.25rem 3rem;
            animation: k2Rise .3s ease-out;
        }
        .k2-section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 1.5rem 0 .75rem;
            font-family: var(--k2-font-display);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
        }
        .k2-section-title::after { content: ''; flex: 1; height: 1px; background: var(--k2-border); }

        /* ── Tarjetas ───────────────────────────────────────────────── */
        .k2-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100%;
            background: var(--k2-surface);
            border: 1px solid var(--k2-border);
            border-radius: var(--k2-radius);
            box-shadow: var(--k2-shadow-sm);
        }
        .k2-card-head {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1rem 1.25rem .75rem;
        }
        .k2-card-title {
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-family: var(--k2-font-display);
            font-size: .95rem;
            font-weight: 700;
        }
        .k2-card-title .bi { color: var(--k2-primary-text); font-size: 1rem; }
        .k2-card-sub { margin-top: 2px; font-size: .74rem; color: var(--k2-ink-3); }
        .k2-card-tools { margin-left: auto; display: flex; align-items: center; gap: .4rem; }
        .k2-card-body { flex: 1; padding: .25rem 1.25rem 1.25rem; }
        .k2-card-body.flush { padding: 0; }
        .k2-card-foot {
            padding: .7rem 1.25rem;
            border-top: 1px solid var(--k2-border);
            border-radius: 0 0 var(--k2-radius) var(--k2-radius);
            background: var(--k2-surface-2);
            font-size: .77rem;
            color: var(--k2-ink-3);
        }

        /* ── KPI ────────────────────────────────────────────────────── */
        .k2-kpi {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            height: 100%;
            padding: 1.1rem 1.25rem;
            text-decoration: none;
            color: inherit;
            transition: transform .15s, box-shadow .15s, border-color .15s;
        }
        .k2-kpi::after {
            content: '';
            position: absolute;
            right: -30px; top: -30px;
            width: 110px; height: 110px;
            border-radius: 50%;
            background: var(--k2-primary-50);
            opacity: .55;
            pointer-events: none;
        }
        .k2-kpi.teal::after   { background: var(--k2-teal-50); }
        .k2-kpi.amber::after  { background: var(--k2-amber-50); }
        .k2-kpi.danger::after { background: var(--k2-danger-50); }
        a.k2-kpi:hover { transform: translateY(-2px); box-shadow: var(--k2-shadow-lg); color: inherit; border-color: var(--k2-primary-100); }
        .k2-kpi-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            font-size: 1.3rem;
            background: var(--k2-primary-50);
            color: var(--k2-primary-text);
            position: relative; z-index: 1;
        }
        .k2-kpi.teal   .k2-kpi-icon { background: var(--k2-teal-50);   color: var(--k2-teal-text); }
        .k2-kpi.amber  .k2-kpi-icon { background: var(--k2-amber-50);  color: var(--k2-amber-text); }
        .k2-kpi.danger .k2-kpi-icon { background: var(--k2-danger-50); color: var(--k2-danger-text); }
        .k2-kpi-body { position: relative; z-index: 1; min-width: 0; }
        .k2-kpi-label {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
        }
        .k2-kpi-value {
            margin-top: .15rem;
            font-family: var(--k2-font-display);
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.02em;
            font-variant-numeric: tabular-nums;
        }
        .k2-kpi-meta { display: flex; align-items: center; gap: .4rem; margin-top: .45rem; font-size: .74rem; color: var(--k2-ink-3); flex-wrap: wrap; }
        .k2-delta {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
        }
        .k2-delta.up   { background: var(--k2-teal-50);   color: var(--k2-teal-text); }
        .k2-delta.down { background: var(--k2-danger-50); color: var(--k2-danger-text); }
        .k2-delta.flat { background: var(--k2-surface-2); color: var(--k2-ink-2); border: 1px solid var(--k2-border); }

        /* ── Badges ─────────────────────────────────────────────────── */
        .k2-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
        }
        .k2-badge.primary { background: var(--k2-primary-50); color: var(--k2-primary-text); }
        .k2-badge.teal    { background: var(--k2-teal-50);    color: var(--k2-teal-text); }
        .k2-badge.amber   { background: var(--k2-amber-50);   color: var(--k2-amber-text); }
        .k2-badge.danger  { background: var(--k2-danger-50);  color: var(--k2-danger-text); }
        .k2-badge.neutral { background: var(--k2-surface-2);  color: var(--k2-ink-2); border: 1px solid var(--k2-border); }
        .k2-badge .k2-pulse {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: k2Pulse 1.6s ease-in-out infinite;
        }
        @keyframes k2Pulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }

        /* ── Tablas ─────────────────────────────────────────────────── */
        .k2-table-wrap { overflow-x: auto; }
        .k2-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
        .k2-table th {
            padding: .6rem 1.25rem;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
            text-align: left;
            white-space: nowrap;
            background: var(--k2-surface-2);
            border-bottom: 1px solid var(--k2-border);
        }
        .k2-table td {
            padding: .7rem 1.25rem;
            border-bottom: 1px solid var(--k2-border);
            vertical-align: middle;
            color: var(--k2-ink);
        }
        .k2-table tbody tr:last-child td { border-bottom: 0; }
        .k2-table tbody tr { transition: background .12s; }
        .k2-table tbody tr:hover { background: var(--k2-surface-2); }
        .k2-table .num { text-align: right; font-variant-numeric: tabular-nums; }
        .k2-table .muted { color: var(--k2-ink-3); font-size: .76rem; }
        .k2-table a { text-decoration: none; }

        /* ── Listas ─────────────────────────────────────────────────── */
        .k2-list { margin: 0; padding: 0; list-style: none; }
        .k2-list-item {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .75rem 1.25rem;
            border-bottom: 1px solid var(--k2-border);
            text-decoration: none;
            color: inherit;
            transition: background .12s;
        }
        .k2-list-item:last-child { border-bottom: 0; }
        a.k2-list-item:hover { background: var(--k2-surface-2); color: inherit; }
        .k2-list-main { flex: 1; min-width: 0; }
        .k2-list-title { font-weight: 600; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .k2-list-meta  { margin-top: 2px; font-size: .74rem; color: var(--k2-ink-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .k2-list-side  { flex-shrink: 0; text-align: right; font-size: .74rem; color: var(--k2-ink-3); }
        .k2-initials {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: var(--k2-primary-50);
            color: var(--k2-primary-text);
            font-weight: 700;
            font-size: .76rem;
        }
        .k2-initials.teal   { background: var(--k2-teal-50);   color: var(--k2-teal-text); }
        .k2-initials.amber  { background: var(--k2-amber-50);  color: var(--k2-amber-text); }
        .k2-initials.danger { background: var(--k2-danger-50); color: var(--k2-danger-text); }

        .k2-attn-count {
            font-family: var(--k2-font-display);
            font-size: 1.3rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--k2-ink);
        }
        .k2-attn-count.zero { color: var(--k2-ink-3); }

        /* ── Línea de tiempo ────────────────────────────────────────── */
        .k2-timeline { position: relative; margin: 0; padding: .35rem 1.25rem 1rem; list-style: none; }
        .k2-timeline::before {
            content: '';
            position: absolute;
            left: calc(1.25rem + 6px);
            top: .8rem; bottom: 1.2rem;
            width: 2px;
            background: var(--k2-border);
        }
        .k2-tl-item { position: relative; padding: .4rem 0 .55rem 1.7rem; font-size: .8rem; }
        .k2-tl-dot {
            position: absolute;
            left: 1px; top: .72rem;
            width: 12px; height: 12px;
            border-radius: 50%;
            background: var(--k2-primary);
            box-shadow: 0 0 0 3px var(--k2-surface);
        }
        .k2-tl-dot.teal   { background: var(--k2-teal); }
        .k2-tl-dot.danger { background: var(--k2-danger); }
        .k2-tl-dot.amber  { background: var(--k2-amber); }
        .k2-tl-text { color: var(--k2-ink); }
        .k2-tl-time { display: block; margin-top: 1px; font-size: .69rem; color: var(--k2-ink-3); }

        /* ── Estado vacío ───────────────────────────────────────────── */
        .k2-empty { padding: 2rem 1rem; text-align: center; color: var(--k2-ink-3); font-size: .82rem; }
        .k2-empty .bi { display: block; margin-bottom: .4rem; font-size: 1.6rem; color: var(--k2-teal); }

        /* ── Botones ────────────────────────────────────────────────── */
        .k2-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            height: 38px;
            padding: 0 .95rem;
            border-radius: 10px;
            border: 1px solid var(--k2-border);
            background: var(--k2-surface);
            color: var(--k2-ink);
            font-family: var(--k2-font);
            font-weight: 600;
            font-size: .82rem;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s, transform .12s, box-shadow .15s;
        }
        .k2-btn:hover { border-color: var(--k2-border-strong); background: var(--k2-surface-2); color: var(--k2-ink); transform: translateY(-1px); }
        .k2-btn-primary { background: var(--k2-primary); border-color: var(--k2-primary); color: #fff; box-shadow: 0 4px 12px rgba(var(--k2-primary-rgb), .25); }
        .k2-btn-primary:hover { background: var(--k2-primary-600); border-color: var(--k2-primary-600); color: #fff; }
        .k2-btn-ghost { border-color: transparent; background: transparent; color: var(--k2-primary-text); }
        .k2-btn-ghost:hover { background: var(--k2-primary-50); border-color: transparent; color: var(--k2-primary-text); }
        .k2-btn-sm { height: 30px; padding: 0 .65rem; font-size: .74rem; border-radius: 8px; }
        .k2-link { color: var(--k2-primary-text); text-decoration: none; font-weight: 600; font-size: .78rem; }
        .k2-link:hover { text-decoration: underline; }

        /* ── Barras de progreso ─────────────────────────────────────── */
        .k2-bar { height: 6px; border-radius: 999px; overflow: hidden; background: var(--k2-surface-2); border: 1px solid var(--k2-border); }
        .k2-bar > span { display: block; height: 100%; border-radius: 999px; background: var(--k2-primary); }
        .k2-bar.teal > span { background: var(--k2-teal); }
        .k2-bar.amber > span { background: var(--k2-amber); }

        /* ── Gráficos ───────────────────────────────────────────────── */
        .k2-chart { position: relative; width: 100%; }

        /* ── Alertas flash ──────────────────────────────────────────── */
        .k2-alert {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: 1rem;
            padding: .85rem 1rem;
            border: 1px solid;
            border-radius: var(--k2-radius-sm);
            font-size: .85rem;
            animation: k2Rise .25s ease-out;
        }
        .k2-alert > .bi { font-size: 1.1rem; flex-shrink: 0; }
        .k2-alert-success { background: var(--k2-teal-50);   border-color: rgba(var(--k2-teal-rgb), .35);   color: var(--k2-teal-text); }
        .k2-alert-warning { background: var(--k2-amber-50);  border-color: rgba(var(--k2-amber-rgb), .45);  color: var(--k2-amber-text); }
        .k2-alert-danger  { background: var(--k2-danger-50); border-color: rgba(var(--k2-danger-rgb), .35); color: var(--k2-danger-text); }
        .k2-alert-close { margin-left: auto; padding: 0; border: 0; background: none; color: inherit; opacity: .6; cursor: pointer; font-size: 1rem; line-height: 1; }
        .k2-alert-close:hover { opacity: 1; }
        .k2-alert ul { margin: .3rem 0 0; padding-left: 1.1rem; }

        /* ── Pie ────────────────────────────────────────────────────── */
        .k2-footer {
            max-width: var(--k2-container);
            margin: 0 auto;
            padding: 1rem 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: .74rem;
            color: var(--k2-ink-3);
        }
        .k2-footer a { color: var(--k2-ink-3); text-decoration: none; margin-left: 1rem; }
        .k2-footer a:hover { color: var(--k2-primary-text); }

        /* ── Paleta de comandos (búsqueda rápida) ───────────────────── */
        .k2-palette .modal-dialog { max-width: 620px; margin-top: 8vh; }
        .k2-palette .modal-content {
            background: var(--k2-surface);
            border: 1px solid var(--k2-border);
            border-radius: 16px;
            box-shadow: var(--k2-shadow-lg);
            overflow: hidden;
        }
        .k2-palette-input { display: flex; align-items: center; gap: .7rem; padding: .9rem 1.1rem; border-bottom: 1px solid var(--k2-border); }
        .k2-palette-input .bi { font-size: 1.1rem; color: var(--k2-ink-3); }
        .k2-palette-input input { flex: 1; border: 0; outline: 0; background: transparent; font-size: 1rem; color: var(--k2-ink); font-family: var(--k2-font); }
        .k2-palette-input input::placeholder { color: var(--k2-ink-3); }
        .k2-palette-list { max-height: 380px; overflow-y: auto; padding: .5rem; }
        .k2-palette-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem .75rem;
            border-radius: 10px;
            text-decoration: none;
            color: var(--k2-ink);
            cursor: pointer;
        }
        .k2-palette-item.is-active, .k2-palette-item:hover { background: var(--k2-primary-50); color: var(--k2-ink); }
        .k2-palette-item .k2-dropdown-icon { width: 30px; height: 30px; font-size: .9rem; }
        .k2-palette-group { margin-left: auto; font-size: .66rem; letter-spacing: .06em; text-transform: uppercase; color: var(--k2-ink-3); }
        .k2-palette-empty { padding: 1.5rem; text-align: center; color: var(--k2-ink-3); font-size: .84rem; }
        .k2-palette-foot { display: flex; gap: 1rem; padding: .55rem 1.1rem; border-top: 1px solid var(--k2-border); font-size: .68rem; color: var(--k2-ink-3); }
        .k2-palette-foot .k2-kbd { margin: 0 .25rem 0 0; }

        /* ── Menú móvil ─────────────────────────────────────────────── */
        .k2-offcanvas {
            --bs-offcanvas-width: 300px;
            --bs-offcanvas-bg: var(--k2-surface);
            --bs-offcanvas-color: var(--k2-ink);
            border-right: 1px solid var(--k2-border);
        }
        .k2-offcanvas .offcanvas-header { border-bottom: 1px solid var(--k2-border); padding: 1rem 1.1rem; }
        .k2-mnav-group {
            padding: .9rem 1rem .3rem;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
        }
        .k2-mnav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin: 0 .5rem;
            padding: .6rem .75rem;
            border-radius: 10px;
            color: var(--k2-ink);
            text-decoration: none;
            font-weight: 600;
            font-size: .86rem;
        }
        .k2-mnav-link .bi { width: 1.2em; text-align: center; color: var(--k2-primary-text); }
        .k2-mnav-link:hover { background: var(--k2-surface-2); color: var(--k2-ink); }
        .k2-mnav-link.active { background: var(--k2-primary-50); color: var(--k2-primary-text); }

        /* ── Responsive ─────────────────────────────────────────────── */
        @media (max-width: 1599.98px) {
            .k2-search { min-width: 0; }
            .k2-search .k2-search-txt, .k2-search .k2-kbd { display: none; }
            .k2-brand-sub { display: none; }
            .k2-inst { max-width: 220px; }
        }
        @media (max-width: 1399.98px) {
            .k2-user-text { display: none; }
            .k2-nav-link { padding: .5rem .65rem; }
            .k2-inst { max-width: 190px; }
        }
        @media (max-width: 991.98px) {
            .k2-nav { display: none; }
            .k2-menu-toggle { display: inline-flex; }
            .k2-inst { display: none; }
            .k2-user-text { display: none; }
            .k2-dropdown-wide { --bs-dropdown-min-width: 300px; }
            .k2-dropdown-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) {
            .k2-topbar-inner { padding: 0 .85rem; gap: .5rem; }
            .k2-search { display: none; }
            .k2-pagehead, .k2-main, .k2-footer { padding-left: .85rem; padding-right: .85rem; }
            .k2-title { font-size: 1.25rem; }
            .k2-table th, .k2-table td { padding-left: .9rem; padding-right: .9rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    $k2User    = auth()->user();
    $k2EsAdmin = $k2User->permisos()->administrador()->tieneTodosLosPermisos();
    $k2Inst    = session('institucion_activa_id')
        ? \App\Models\Institucion::find(session('institucion_activa_id'))
        : null;
    $k2SoloIds = $k2EsAdmin
        ? null
        : $k2User->rolesInstitucion()->vigente()->pluck('id_institucion')->map(fn ($v) => (int) $v)->toArray();
    $k2ListaInst = \App\Models\Institucion::listaJerarquica($k2SoloIds);

    // Avisos: pendientes que requieren intervención del administrador
    $k2Notif = [
        [
            'label'   => 'Licencias pendientes de aprobación',
            'count'   => \App\Models\Licencia::enEstado('pendiente')->count(),
            'icon'    => 'bi-calendar-check',
            'url'     => route('licencias.index', ['estado' => 'pendiente']),
            'variant' => 'amber',
        ],
        [
            'label'   => 'Tickets con novedades',
            'count'   => \App\Models\Ticket::contarNoLeidosParaUsuario($k2User),
            'icon'    => 'bi-life-preserver',
            'url'     => route('tickets.index'),
            'variant' => 'primary',
        ],
    ];
    if ($k2EsAdmin) {
        $k2Notif[] = [
            'label'   => 'Errores de servidor activos',
            'count'   => \App\Models\ErrorServidor::activos()->count(),
            'icon'    => 'bi-bug',
            'url'     => route('admin.errores-servidor.index'),
            'variant' => 'danger',
        ];
    }
    $k2NotifTotal = array_sum(array_column($k2Notif, 'count'));

    // Estructura del menú: alimenta el nav de escritorio, el menú móvil y la búsqueda rápida
    $k2Menu = [
        [
            'label'  => 'Panel',
            'icon'   => 'bi-grid-1x2-fill',
            'url'    => route('panel.index'),
            'active' => ['panel.*'],
        ],
        [
            'label'  => 'Personal',
            'icon'   => 'bi-people-fill',
            'active' => ['usuarios.*', 'designaciones.*', 'ddjj.*', 'licencias.*', 'avisos.*', 'marcas.*', 'banco-horas.*'],
            'cols'   => 2,
            'items'  => [
                ['label' => 'Usuarios',            'desc' => 'Altas, datos y roles',           'icon' => 'bi-person-badge',      'url' => route('usuarios.index'),      'active' => ['usuarios.*']],
                ['label' => 'Designaciones',       'desc' => 'Cargos y dependencias',          'icon' => 'bi-briefcase',         'url' => route('designaciones.index'), 'active' => ['designaciones.*']],
                ['label' => 'DDJJ',                'desc' => 'Declaraciones de horario',       'icon' => 'bi-file-earmark-text', 'url' => route('ddjj.index'),          'active' => ['ddjj.*']],
                ['label' => 'Licencias y permisos','desc' => 'Solicitudes y aprobaciones',     'icon' => 'bi-calendar-check',    'url' => route('licencias.index'),     'active' => ['licencias.*']],
                ['label' => 'Avisos del personal', 'desc' => 'Novedades comunicadas',          'icon' => 'bi-megaphone',         'url' => route('avisos.index'),        'active' => ['avisos.*']],
                ['label' => 'Marcas',              'desc' => 'Registros de fichaje',           'icon' => 'bi-fingerprint',       'url' => route('marcas.index'),        'active' => ['marcas.*']],
                ['label' => 'Banco de horas',      'desc' => 'Saldos y ajustes',               'icon' => 'bi-bank',              'url' => route('banco-horas.index'),   'active' => ['banco-horas.*']],
            ],
        ],
        [
            'label'  => 'Institución',
            'icon'   => 'bi-building-fill',
            'active' => ['instituciones.*', 'dependencias.*', 'edificios.*', 'oficinas.*', 'dispositivos.*', 'cargos.*', 'categorias-cargo.*', 'roles.*', 'tipos-licencia.*', 'calendario.*'],
            'cols'   => 2,
            'items'  => [
                ['label' => 'Instituciones',       'desc' => 'Jerarquía y configuración',      'icon' => 'bi-building-gear',     'url' => route('instituciones.index'),    'active' => ['instituciones.*']],
                ['label' => 'Dependencias',        'desc' => 'Estructura y jefaturas',         'icon' => 'bi-diagram-3',         'url' => route('dependencias.index'),     'active' => ['dependencias.*']],
                ['label' => 'Edificios',           'desc' => 'Complejos y sedes',              'icon' => 'bi-buildings',         'url' => route('edificios.index'),        'active' => ['edificios.*']],
                ['label' => 'Oficinas y aulas',    'desc' => 'Espacios físicos',               'icon' => 'bi-door-open',         'url' => route('oficinas.index'),         'active' => ['oficinas.*']],
                ['label' => 'Dispositivos',        'desc' => 'Relojes y terminales',           'icon' => 'bi-hdd-network',       'url' => route('dispositivos.index'),     'active' => ['dispositivos.*']],
                ['label' => 'Cargos',              'desc' => 'Cargos y cargas horarias',       'icon' => 'bi-award',             'url' => route('cargos.index'),           'active' => ['cargos.*']],
                ['label' => 'Categorías de cargo', 'desc' => 'Agrupación de cargos',           'icon' => 'bi-tags',              'url' => route('categorias-cargo.index'), 'active' => ['categorias-cargo.*']],
                ['label' => 'Roles y permisos',    'desc' => 'Accesos por módulo',             'icon' => 'bi-shield-check',      'url' => route('roles.index'),            'active' => ['roles.*']],
                ['label' => 'Tipos de licencia',   'desc' => 'Catálogo de licencias',          'icon' => 'bi-card-list',         'url' => route('tipos-licencia.index'),   'active' => ['tipos-licencia.*']],
                ['label' => 'Calendario',          'desc' => 'Feriados y eventos',             'icon' => 'bi-calendar-event',    'url' => route('calendario.index'),       'active' => ['calendario.*']],
            ],
        ],
        [
            'label'  => 'Informes',
            'icon'   => 'bi-bar-chart-fill',
            'active' => ['informes.*'],
            'items'  => [
                ['label' => 'Mensual de marcas',       'desc' => 'Fichajes computados por mes',  'icon' => 'bi-clock-history',     'url' => route('informes.marcas'),              'active' => ['informes.marcas']],
                ['label' => 'General del usuario',     'desc' => 'Informe individual',           'icon' => 'bi-person-lines-fill', 'url' => route('informes.index'),               'active' => ['informes.index', 'informes.show']],
                ['label' => 'Resumen por dependencia', 'desc' => 'Asistencia agregada',          'icon' => 'bi-diagram-3-fill',    'url' => route('informes.resumen-dependencia'), 'active' => ['informes.resumen-dependencia']],
            ],
        ],
        [
            'label'  => 'Soporte',
            'icon'   => 'bi-life-preserver',
            'active' => ['tickets.*', 'logs.*', 'admin.errores-servidor.*'],
            'items'  => array_values(array_filter([
                ['label' => 'Tickets',                'desc' => 'Mesa de ayuda',              'icon' => 'bi-life-preserver', 'url' => route('tickets.index'),                 'active' => ['tickets.index', 'tickets.show', 'tickets.create']],
                $k2EsAdmin ? ['label' => 'Categorías de tickets', 'desc' => 'Clasificación de soporte', 'icon' => 'bi-tags-fill',      'url' => route('tickets.categorias.index'),      'active' => ['tickets.categorias.*']] : null,
                $k2EsAdmin ? ['label' => 'Errores de servidor',   'desc' => 'Incidencias técnicas',     'icon' => 'bi-bug',            'url' => route('admin.errores-servidor.index'),  'active' => ['admin.errores-servidor.*'], 'danger' => true] : null,
                ['label' => 'Logs de actividad',      'desc' => 'Auditoría del sistema',      'icon' => 'bi-journal-text',   'url' => route('logs.index'),                    'active' => ['logs.*']],
                $k2EsAdmin ? ['label' => 'Acceso a base de datos', 'desc' => 'phpMyAdmin',             'icon' => 'bi-database-gear',  'url' => config('kairos.phpmyadmin_url'),        'active' => [], 'external' => true] : null,
            ])),
        ],
    ];

    // Lista plana para la búsqueda rápida
    $k2Palette = [];
    foreach ($k2Menu as $grupo) {
        if (isset($grupo['items'])) {
            foreach ($grupo['items'] as $it) {
                $k2Palette[] = ['label' => $it['label'], 'desc' => $it['desc'] ?? '', 'icon' => $it['icon'], 'url' => $it['url'], 'group' => $grupo['label'], 'external' => $it['external'] ?? false];
            }
        } else {
            $k2Palette[] = ['label' => $grupo['label'], 'desc' => '', 'icon' => $grupo['icon'], 'url' => $grupo['url'], 'group' => 'General', 'external' => false];
        }
    }
    $k2Palette[] = ['label' => 'Mi perfil',      'desc' => 'Datos personales y métodos de acceso', 'icon' => 'bi-person-circle', 'url' => route('perfil'),                  'group' => 'Cuenta', 'external' => false];
    $k2Palette[] = ['label' => 'Vista clásica',  'desc' => 'Volver al dashboard anterior',          'icon' => 'bi-layout-text-window', 'url' => route('home', ['vista' => 'admin']), 'group' => 'Cuenta', 'external' => false];

    $k2Iniciales = strtoupper(substr($k2User->nombres ?? 'U', 0, 1)) . strtoupper(substr($k2User->apellidos ?? '', 0, 1));
@endphp

{{-- ══ BARRA SUPERIOR ══════════════════════════════════════════════════════ --}}
<header class="k2-topbar">
    <div class="k2-topbar-inner">

        <button class="k2-iconbtn k2-menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#k2MobileNav" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>

        <a href="{{ route('panel.index') }}" class="k2-brand">
            <span class="k2-brand-mark">K</span>
            <span>
                <span class="k2-brand-name">KAIROS</span>
                <span class="k2-brand-sub">Panel de administración</span>
            </span>
        </a>

        <nav class="k2-navwrap" aria-label="Principal">
            @include('layouts._nav2', ['modo' => 'desktop', 'menu' => $k2Menu])
        </nav>

        <div class="k2-actions">

            {{-- Búsqueda rápida --}}
            <button class="k2-search" type="button" data-bs-toggle="modal" data-bs-target="#k2Palette" aria-label="Búsqueda rápida">
                <i class="bi bi-search"></i>
                <span class="k2-search-txt">Ir a un módulo…</span>
                <span class="k2-kbd">Ctrl K</span>
            </button>

            {{-- Institución activa --}}
            @if(count($k2ListaInst) > 1)
                <div class="dropdown">
                    <button class="k2-inst dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar institución activa">
                        <i class="bi bi-building"></i>
                        <span class="k2-inst-name">{{ $k2Inst?->nombre ?? 'Seleccionar institución' }}</span>
                        <i class="bi bi-chevron-down k2-caret"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end k2-dropdown">
                        <div class="k2-dropdown-head">Institución activa</div>
                        <div class="k2-inst-list">
                            @foreach($k2ListaInst as $item)
                                @php $inst = $item['institucion']; $nivel = $item['nivel']; @endphp
                                <form method="POST" action="{{ route('institucion-activa.cambiar') }}">
                                    @csrf
                                    <input type="hidden" name="id_institucion" value="{{ $inst->id }}">
                                    <button type="submit" class="k2-dropdown-item {{ $k2Inst?->id === $inst->id ? 'active' : '' }}"
                                            style="padding-left: {{ .65 + $nivel * 1.1 }}rem">
                                        <span class="k2-dropdown-icon" style="width:28px;height:28px;font-size:.85rem">
                                            @if($k2Inst?->id === $inst->id)
                                                <i class="bi bi-check2"></i>
                                            @elseif($nivel === 0)
                                                <i class="bi bi-building"></i>
                                            @else
                                                <i class="bi bi-diagram-2"></i>
                                            @endif
                                        </span>
                                        <span class="k2-dropdown-text">
                                            <span class="k2-dropdown-label">{{ $inst->nombre }}</span>
                                            @if($inst->sigla)<span class="k2-dropdown-desc">{{ $inst->sigla }}</span>@endif
                                        </span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif(count($k2ListaInst) === 1)
                <span class="k2-inst" style="cursor:default">
                    <i class="bi bi-building"></i>
                    <span class="k2-inst-name">{{ $k2Inst?->nombre ?? $k2ListaInst[0]['institucion']->nombre }}</span>
                </span>
            @endif

            {{-- Tema claro / oscuro --}}
            <button class="k2-iconbtn" type="button" id="k2ThemeToggle" title="Cambiar tema" aria-label="Cambiar tema">
                <i class="bi bi-moon-stars"></i>
            </button>

            {{-- Notificaciones --}}
            <div class="dropdown">
                <button class="k2-iconbtn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pendientes">
                    <i class="bi bi-bell"></i>
                    @if($k2NotifTotal > 0)
                        <span class="k2-dot">{{ $k2NotifTotal > 99 ? '99+' : $k2NotifTotal }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end k2-dropdown k2-notif">
                    <div class="k2-dropdown-head">Requieren atención</div>
                    @foreach($k2Notif as $n)
                        <a href="{{ $n['url'] }}" class="k2-notif-item {{ $n['count'] === 0 ? 'muted' : '' }}">
                            <span class="k2-initials {{ $n['variant'] }}"><i class="bi {{ $n['icon'] }}"></i></span>
                            <span class="k2-dropdown-label" style="font-weight:500">{{ $n['label'] }}</span>
                            <span class="k2-notif-count">{{ $n['count'] }}</span>
                        </a>
                    @endforeach
                    @if($k2NotifTotal === 0)
                        <div class="k2-empty" style="padding:1rem"><i class="bi bi-check2-circle"></i>Todo al día</div>
                    @endif
                </div>
            </div>

            {{-- Usuario --}}
            <div class="dropdown">
                <button class="k2-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if($k2User->foto)
                        <img src="{{ asset('storage/' . $k2User->foto) }}" alt="" class="k2-avatar">
                    @else
                        <span class="k2-avatar-ph">{{ $k2Iniciales }}</span>
                    @endif
                    <span class="k2-user-text">
                        <span class="k2-user-name">{{ $k2User->apellidos }}, {{ $k2User->nombres }}</span>
                        <span class="k2-user-role">{{ $k2EsAdmin ? 'Administrador General' : 'Usuario' }}</span>
                    </span>
                    <i class="bi bi-chevron-down k2-caret" style="font-size:.6rem;opacity:.6"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end k2-dropdown" style="--bs-dropdown-min-width:240px">
                    <div class="k2-dropdown-head">{{ $k2User->email ?? $k2User->documento }}</div>
                    <a class="k2-dropdown-item {{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}">
                        <span class="k2-dropdown-icon"><i class="bi bi-person-circle"></i></span>
                        <span class="k2-dropdown-text"><span class="k2-dropdown-label">Mi perfil</span></span>
                    </a>
                    <a class="k2-dropdown-item" href="{{ route('home', ['vista' => 'admin']) }}">
                        <span class="k2-dropdown-icon"><i class="bi bi-layout-text-window"></i></span>
                        <span class="k2-dropdown-text">
                            <span class="k2-dropdown-label">Vista clásica</span>
                            <span class="k2-dropdown-desc">Dashboard anterior</span>
                        </span>
                    </a>
                    <div class="k2-dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="k2-dropdown-item danger">
                            <span class="k2-dropdown-icon"><i class="bi bi-box-arrow-right"></i></span>
                            <span class="k2-dropdown-text"><span class="k2-dropdown-label" style="color:var(--k2-danger-text)">Cerrar sesión</span></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ══ MENÚ MÓVIL ══════════════════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start k2-offcanvas" tabindex="-1" id="k2MobileNav" aria-labelledby="k2MobileNavLabel">
    <div class="offcanvas-header">
        <a href="{{ route('panel.index') }}" class="k2-brand" id="k2MobileNavLabel">
            <span class="k2-brand-mark">K</span>
            <span><span class="k2-brand-name">KAIROS</span></span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0 pb-4">
        @include('layouts._nav2', ['modo' => 'mobile', 'menu' => $k2Menu])
    </div>
</div>

{{-- ══ ENCABEZADO DE PÁGINA ════════════════════════════════════════════════ --}}
@hasSection('page-title')
<div class="k2-pagehead">
    <div>
        <ol class="k2-crumbs">
            <li><a href="{{ route('panel.index') }}"><i class="bi bi-house-door"></i></a></li>
            @yield('breadcrumb')
        </ol>
        <h1 class="k2-title">@yield('page-title')</h1>
        @hasSection('page-subtitle')
            <p class="k2-subtitle">@yield('page-subtitle')</p>
        @endif
    </div>
    @hasSection('page-actions')
        <div class="k2-page-actions">@yield('page-actions')</div>
    @endif
</div>
@endif

{{-- ══ CONTENIDO ═══════════════════════════════════════════════════════════ --}}
<main class="k2-main">

    @if(session('success'))
        <div class="k2-alert k2-alert-success" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="k2-alert-close" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="k2-alert k2-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="k2-alert-close" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="k2-alert k2-alert-warning" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="k2-alert-close" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif
    @if($errors->any())
        <div class="k2-alert k2-alert-danger" role="alert">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <strong>Corrija los siguientes errores:</strong>
                <ul>
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            <button type="button" class="k2-alert-close" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    @yield('content')

</main>

{{-- ══ PIE ═════════════════════════════════════════════════════════════════ --}}
<footer class="k2-footer">
    <span>KAIROS v1.0 · Sistema de Control Horario Institucional</span>
    <span>
        <a href="{{ route('home', ['vista' => 'admin']) }}">Vista clásica</a>
        <a href="{{ route('tickets.index') }}">Soporte</a>
        <a href="#">Documentación</a>
    </span>
</footer>

{{-- ══ BÚSQUEDA RÁPIDA (Ctrl+K) ════════════════════════════════════════════ --}}
<div class="modal fade k2-palette" id="k2Palette" tabindex="-1" aria-labelledby="k2PaletteInput" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="k2-palette-input">
                <i class="bi bi-search"></i>
                <input type="text" id="k2PaletteInput" placeholder="Buscar módulo o acción…" autocomplete="off" spellcheck="false">
                <span class="k2-kbd">Esc</span>
            </div>
            <div class="k2-palette-list" id="k2PaletteList"></div>
            <div class="k2-palette-foot">
                <span><span class="k2-kbd">↑↓</span> navegar</span>
                <span><span class="k2-kbd">Enter</span> abrir</span>
                <span><span class="k2-kbd">Esc</span> cerrar</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── Tema ───────────────────────────────────────────────────────── */
    var root   = document.documentElement;
    var toggle = document.getElementById('k2ThemeToggle');

    function aplicarIconoTema() {
        var oscuro = root.getAttribute('data-theme') === 'dark';
        toggle.innerHTML = oscuro ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
        toggle.title = oscuro ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro';
    }
    toggle.addEventListener('click', function () {
        var oscuro = root.getAttribute('data-theme') !== 'dark';
        if (oscuro) {
            root.setAttribute('data-theme', 'dark');
            root.setAttribute('data-bs-theme', 'dark');
        } else {
            root.removeAttribute('data-theme');
            root.setAttribute('data-bs-theme', 'light');
        }
        try { localStorage.setItem('k2-theme', oscuro ? 'dark' : 'light'); } catch (e) {}
        aplicarIconoTema();
        window.dispatchEvent(new CustomEvent('k2:theme', { detail: { dark: oscuro } }));
    });
    aplicarIconoTema();

    /* Helpers para que los gráficos lean los colores del tema actual */
    window.k2 = window.k2 || {};
    window.k2.cssVar = function (name) {
        return getComputedStyle(root).getPropertyValue(name).trim();
    };
    window.k2.isDark = function () { return root.getAttribute('data-theme') === 'dark'; };

    if (window.Chart) {
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size   = 11;
    }

    /* ── Alertas flash: cierre manual y automático ───────────────────── */
    document.querySelectorAll('.k2-alert').forEach(function (el) {
        var cerrar = function () {
            el.style.transition = 'opacity .2s, transform .2s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-4px)';
            setTimeout(function () { el.remove(); }, 200);
        };
        var btn = el.querySelector('.k2-alert-close');
        if (btn) btn.addEventListener('click', cerrar);
        if (!el.classList.contains('k2-alert-danger')) setTimeout(cerrar, 7000);
    });

    /* ── Dropdowns con posicionamiento fijo (evitan recortes) ─────────── */
    document.querySelectorAll('.k2-topbar [data-bs-toggle="dropdown"]').forEach(function (el) {
        new bootstrap.Dropdown(el, { popperConfig: { strategy: 'fixed' } });
    });

    /* ── Menú móvil: cerrar al navegar ──────────────────────────────── */
    document.querySelectorAll('#k2MobileNav a.k2-mnav-link').forEach(function (a) {
        a.addEventListener('click', function () {
            var oc = bootstrap.Offcanvas.getInstance(document.getElementById('k2MobileNav'));
            if (oc) oc.hide();
        });
    });

    /* ── Búsqueda rápida (paleta de comandos) ───────────────────────── */
    var items    = @json($k2Palette);
    var modalEl  = document.getElementById('k2Palette');
    var modal    = bootstrap.Modal.getOrCreateInstance(modalEl);
    var input    = document.getElementById('k2PaletteInput');
    var list     = document.getElementById('k2PaletteList');
    var activo   = 0;
    var visibles = [];

    function normalizar(s) {
        return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }
    function escapar(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function render(q) {
        var nq = normalizar(q);
        visibles = items.filter(function (it) {
            if (!nq) return true;
            return normalizar(it.label + ' ' + it.desc + ' ' + it.group).indexOf(nq) !== -1;
        });
        activo = 0;
        if (!visibles.length) {
            list.innerHTML = '<div class="k2-palette-empty"><i class="bi bi-search me-1"></i>Sin resultados para «' + escapar(q) + '»</div>';
            return;
        }
        list.innerHTML = visibles.map(function (it, i) {
            return '<a class="k2-palette-item' + (i === 0 ? ' is-active' : '') + '" href="' + escapar(it.url) + '"'
                + (it.external ? ' target="_blank" rel="noopener noreferrer"' : '') + ' data-i="' + i + '">'
                + '<span class="k2-dropdown-icon"><i class="bi ' + escapar(it.icon) + '"></i></span>'
                + '<span class="k2-dropdown-text"><span class="k2-dropdown-label">' + escapar(it.label) + '</span>'
                + (it.desc ? '<span class="k2-dropdown-desc">' + escapar(it.desc) + '</span>' : '') + '</span>'
                + '<span class="k2-palette-group">' + escapar(it.group) + '</span>'
                + '</a>';
        }).join('');
    }
    function marcarActivo() {
        var nodos = list.querySelectorAll('.k2-palette-item');
        nodos.forEach(function (n, i) { n.classList.toggle('is-active', i === activo); });
        if (nodos[activo]) nodos[activo].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('input', function () { render(input.value); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); activo = Math.min(activo + 1, visibles.length - 1); marcarActivo(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activo = Math.max(activo - 1, 0); marcarActivo(); }
        else if (e.key === 'Enter') {
            e.preventDefault();
            var it = visibles[activo];
            if (!it) return;
            if (it.external) window.open(it.url, '_blank', 'noopener'); else window.location.href = it.url;
        }
    });
    list.addEventListener('mousemove', function (e) {
        var a = e.target.closest('.k2-palette-item');
        if (a && +a.dataset.i !== activo) { activo = +a.dataset.i; marcarActivo(); }
    });
    modalEl.addEventListener('show.bs.modal',  function () { input.value = ''; render(''); });
    modalEl.addEventListener('shown.bs.modal', function () { input.focus(); });

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            modal.toggle();
        }
    });
})();
</script>
@stack('scripts')
</body>
</html>
