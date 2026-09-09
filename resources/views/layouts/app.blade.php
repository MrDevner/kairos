<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · Kairos</title>

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════════════
           KAIROS · Layout principal — tokens de diseño
           Paleta institucional: azul #2E5EAA · verde-agua #4CAF93
           · acento #F2A65A · texto #2B2D42
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
            --k2-amber-600:      #D08A3A;
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
            --k2-bg-rgb:         244, 246, 250;
            --k2-ink-rgb:        43, 45, 66;
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

            /* ── Alias retro-compatibles: usados inline en las vistas ─── */
            --azul:                 var(--k2-primary-text);
            --azul-light:           var(--k2-primary-text);
            --celeste:              var(--k2-teal);
            --dorado:               var(--k2-amber);
            --blanco:               var(--k2-surface);
            --gris-bg:              var(--k2-bg);
            --gris-texto:           var(--k2-ink);
            --gris-borde:           var(--k2-border);
            --kairos-primary-light: var(--k2-primary-50);
            --kairos-text:          var(--k2-ink);
            --kairos-bg:            var(--k2-bg);
            --kairos-accent:        var(--k2-amber);
            --kairos-accent-rgb:    var(--k2-amber-rgb);
            --kairos-radius:        var(--k2-radius);
            --kairos-radius-sm:     var(--k2-radius-sm);
            --kairos-radius-lg:     16px;
            --kairos-gradient:      linear-gradient(135deg, var(--k2-primary) 0%, var(--k2-primary-700) 100%);
            --kairos-gradient-soft: linear-gradient(135deg, rgba(46,94,170,.08) 0%, rgba(76,175,147,.08) 100%);
            --kairos-shadow-sm:     var(--k2-shadow-sm);
            --kairos-shadow-md:     var(--k2-shadow-sm);
            --kairos-shadow-lg:     var(--k2-shadow-lg);

            /* ── Bootstrap: toma la paleta y los radios del sistema ───── */
            --bs-primary:        var(--k2-primary);
            --bs-primary-rgb:    var(--k2-primary-rgb);
            --bs-secondary:      var(--k2-teal);
            --bs-secondary-rgb:  var(--k2-teal-rgb);
            --bs-success:        var(--k2-teal);
            --bs-success-rgb:    var(--k2-teal-rgb);
            --bs-warning:        var(--k2-amber);
            --bs-warning-rgb:    var(--k2-amber-rgb);
            --bs-danger:         var(--k2-danger);
            --bs-danger-rgb:     var(--k2-danger-rgb);
            --bs-info:           var(--k2-primary);
            --bs-info-rgb:       var(--k2-primary-rgb);
            --bs-light:          var(--k2-surface-2);
            --bs-dark:           var(--k2-ink);
            --bs-body-bg:        var(--k2-bg);
            --bs-body-bg-rgb:    var(--k2-bg-rgb);
            --bs-body-color:     var(--k2-ink);
            --bs-body-color-rgb: var(--k2-ink-rgb);
            --bs-emphasis-color: var(--k2-ink);
            --bs-heading-color:  var(--k2-ink);
            --bs-secondary-color: var(--k2-ink-3);
            --bs-tertiary-color:  var(--k2-ink-3);
            --bs-secondary-bg:   var(--k2-surface-2);
            --bs-tertiary-bg:    var(--k2-surface-2);
            --bs-border-color:   var(--k2-border);
            --bs-border-color-translucent: var(--k2-border);
            --bs-link-color:     var(--k2-primary-text);
            --bs-link-color-rgb: var(--k2-primary-rgb);
            --bs-link-hover-color: var(--k2-primary-600);
            --bs-border-radius:    var(--k2-radius-sm);
            --bs-border-radius-sm: var(--k2-radius-xs);
            --bs-border-radius-lg: var(--k2-radius);
            --bs-font-sans-serif:  var(--k2-font);
            --bs-body-font-size:   .9rem;
        }

        html[data-theme="dark"] {
            --k2-primary-text:   #8FB3EA;
            --k2-primary-50:     rgba(46, 94, 170, .22);
            --k2-primary-100:    rgba(46, 94, 170, .34);
            --k2-teal-50:        rgba(76, 175, 147, .18);
            --k2-teal-text:      #7ED4BA;
            --k2-amber-50:       rgba(242, 166, 90, .18);
            --k2-amber-text:     #F5C289;
            --k2-amber-600:      #F2A65A;
            --k2-danger-50:      rgba(214, 69, 80, .2);
            --k2-danger-text:    #F0919A;

            --k2-ink:            #E7EBF3;
            --k2-ink-2:          #A9B1C3;
            --k2-ink-3:          #7E879B;
            --k2-ink-rgb:        231, 235, 243;

            --k2-bg:             #0F1320;
            --k2-bg-rgb:         15, 19, 32;
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
        hr { border-color: var(--k2-border); opacity: 1; }

        @keyframes k2Rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        /* Nota: no animar `transform` en los dropdowns. Popper los posiciona con transform
           (strategy fixed) y una animación sobre esa propiedad los hace aparecer en (0,0)
           antes de acomodarse. Se usa la propiedad independiente `translate`. */
        @keyframes k2Pop  { from { opacity: 0; translate: 0 -6px; } to { opacity: 1; translate: 0 0; } }

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

        /* ── Dropdowns de la barra ──────────────────────────────────── */
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
            padding: 1.4rem 1.25rem .35rem;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .k2-crumbs {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .4rem;
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: .75rem;
            color: var(--k2-ink-3);
        }
        .k2-crumbs a { color: var(--k2-ink-3); text-decoration: none; }
        .k2-crumbs a:hover { color: var(--k2-primary-text); }
        .k2-crumbs li + li::before { content: '/'; margin-right: .4rem; opacity: .5; }
        .k2-crumbs li.active { color: var(--k2-ink-2); font-weight: 600; }
        /* Compatibilidad con el marcado Bootstrap (.breadcrumb-item) usado por las vistas */
        .k2-crumbs .breadcrumb-item { padding-left: 0; }
        .k2-crumbs .breadcrumb-item + .breadcrumb-item::before { content: '/'; padding-right: 0; margin-right: .4rem; color: inherit; opacity: .5; float: none; }
        .k2-crumbs .breadcrumb-item.active { color: var(--k2-ink-2); font-weight: 600; }
        .k2-pagehead-titles { margin-top: .5rem; }
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

        /* Encabezado "legado" que usan las vistas dentro de su contenido */
        .k2-page-title {
            margin: 0;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
            font-family: var(--k2-font-display);
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -.01em;
            color: var(--k2-ink);
        }
        .k2-page-title > .bi { color: var(--k2-primary-text); font-size: 1.15rem; }
        .k2-page-title .badge { font-size: .62rem; }

        /* ── Contenido ──────────────────────────────────────────────── */
        .k2-main {
            max-width: var(--k2-container);
            margin: 0 auto;
            padding: .75rem 1.25rem 3rem;
            min-height: calc(100vh - 260px);
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

        /* ── Tarjetas propias ───────────────────────────────────────── */
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
        .k2-kpi-value.sm { font-size: 1.3rem; }
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

        /* ── Badges propias ─────────────────────────────────────────── */
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

        /* ── Tablas propias ─────────────────────────────────────────── */
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
        .k2-list-item.hoy { background: var(--k2-amber-50); }
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
        .k2-date {
            width: 44px;
            flex-shrink: 0;
            text-align: center;
            border-radius: 10px;
            padding: .3rem 0;
            background: var(--k2-primary-50);
            color: var(--k2-primary-text);
            line-height: 1.05;
        }
        .k2-date b { display: block; font-family: var(--k2-font-display); font-size: 1.05rem; font-weight: 800; }
        .k2-date small { display: block; font-size: .6rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }

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

        /* ── Botones propios ────────────────────────────────────────── */
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
        .k2-btn-teal { background: var(--k2-teal); border-color: var(--k2-teal); color: #fff; }
        .k2-btn-teal:hover { background: #41957D; border-color: #41957D; color: #fff; }
        .k2-btn-ghost { border-color: transparent; background: transparent; color: var(--k2-primary-text); }
        .k2-btn-ghost:hover { background: var(--k2-primary-50); border-color: transparent; color: var(--k2-primary-text); }
        .k2-btn-sm { height: 30px; padding: 0 .65rem; font-size: .74rem; border-radius: 8px; }
        .k2-link { color: var(--k2-primary-text); text-decoration: none; font-weight: 600; font-size: .78rem; }
        .k2-link:hover { text-decoration: underline; }

        /* ── Barras de progreso propias ─────────────────────────────── */
        .k2-bar { height: 6px; border-radius: 999px; overflow: hidden; background: var(--k2-surface-2); border: 1px solid var(--k2-border); }
        .k2-bar > span { display: block; height: 100%; border-radius: 999px; background: var(--k2-primary); }
        .k2-bar.teal > span { background: var(--k2-teal); }
        .k2-bar.amber > span { background: var(--k2-amber); }

        /* ── Gráficos ───────────────────────────────────────────────── */
        .k2-chart { position: relative; width: 100%; }

        /* ── Alertas flash del layout ───────────────────────────────── */
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

        /* ═══════════════════════════════════════════════════════════════
           Componentes Bootstrap usados por las vistas, reestilados
           ═══════════════════════════════════════════════════════════════ */

        /* Tarjetas */
        .card {
            --bs-card-bg: var(--k2-surface);
            --bs-card-color: var(--k2-ink);
            --bs-card-border-color: var(--k2-border);
            --bs-card-border-radius: var(--k2-radius);
            --bs-card-inner-border-radius: calc(var(--k2-radius) - 1px);
            --bs-card-cap-bg: var(--k2-surface);
            --bs-card-cap-color: var(--k2-ink);
            --bs-card-cap-padding-y: .85rem;
            --bs-card-cap-padding-x: 1.15rem;
            box-shadow: var(--k2-shadow-sm);
        }
        .card-header {
            font-family: var(--k2-font-display);
            font-weight: 700;
            font-size: .92rem;
            border-bottom: 1px solid var(--k2-border);
        }
        .card-header > .bi:first-child, .card-header > i.bi { color: var(--k2-primary-text); }
        .card-header.text-bg-danger { background: var(--k2-danger-50) !important; color: var(--k2-danger-text) !important; }
        .card-header.text-bg-danger > .bi { color: inherit; }
        .card-header .btn-light, .card-header .btn-outline-light {
            --bs-btn-bg: var(--k2-surface-2);
            --bs-btn-color: var(--k2-ink);
            --bs-btn-border-color: var(--k2-border);
            --bs-btn-hover-bg: var(--k2-primary-50);
            --bs-btn-hover-color: var(--k2-primary-text);
            --bs-btn-hover-border-color: var(--k2-primary-100);
            --bs-btn-active-bg: var(--k2-primary-50);
            --bs-btn-active-color: var(--k2-primary-text);
            --bs-btn-active-border-color: var(--k2-primary-100);
        }
        .card-header .text-white, .card-header .text-light { color: var(--k2-ink-2) !important; }
        .card-header .btn-link.text-white { color: var(--k2-primary-text) !important; }
        .card-footer { background: var(--k2-surface-2); border-top: 1px solid var(--k2-border); color: var(--k2-ink-2); }
        .card-body.p-0 .table > tbody > tr:last-child > td { border-bottom: 0; }
        .card-body.p-0 .table { margin-bottom: 0; }
        .card-title { font-family: var(--k2-font-display); font-weight: 700; }

        /* Botones */
        .btn {
            --bs-btn-border-radius: 10px;
            --bs-btn-font-weight: 600;
            --bs-btn-font-size: .84rem;
            --bs-btn-padding-x: .95rem;
            --bs-btn-padding-y: .48rem;
            --bs-btn-focus-box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .18);
            transition: background-color .15s, border-color .15s, color .15s, transform .12s, box-shadow .15s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: none; }
        .btn-sm, .btn-group-sm > .btn { --bs-btn-font-size: .76rem; --bs-btn-padding-x: .7rem; --bs-btn-padding-y: .34rem; --bs-btn-border-radius: 8px; }
        .btn-lg { --bs-btn-border-radius: 12px; }
        .btn-primary {
            --bs-btn-bg: var(--k2-primary); --bs-btn-border-color: var(--k2-primary);
            --bs-btn-hover-bg: var(--k2-primary-600); --bs-btn-hover-border-color: var(--k2-primary-600);
            --bs-btn-active-bg: var(--k2-primary-700); --bs-btn-active-border-color: var(--k2-primary-700);
            --bs-btn-disabled-bg: var(--k2-primary); --bs-btn-disabled-border-color: var(--k2-primary);
            box-shadow: 0 3px 10px rgba(var(--k2-primary-rgb), .22);
        }
        .btn-outline-primary {
            --bs-btn-color: var(--k2-primary-text); --bs-btn-border-color: var(--k2-primary-100); --bs-btn-bg: var(--k2-surface);
            --bs-btn-hover-bg: var(--k2-primary-50); --bs-btn-hover-color: var(--k2-primary-text); --bs-btn-hover-border-color: var(--k2-primary-100);
            --bs-btn-active-bg: var(--k2-primary-100); --bs-btn-active-color: var(--k2-primary-text); --bs-btn-active-border-color: var(--k2-primary-100);
        }
        .btn-outline-secondary, .btn-light, .btn-outline-dark, .btn-outline-light {
            --bs-btn-color: var(--k2-ink); --bs-btn-bg: var(--k2-surface); --bs-btn-border-color: var(--k2-border);
            --bs-btn-hover-bg: var(--k2-surface-2); --bs-btn-hover-color: var(--k2-ink); --bs-btn-hover-border-color: var(--k2-border-strong);
            --bs-btn-active-bg: var(--k2-surface-2); --bs-btn-active-color: var(--k2-ink); --bs-btn-active-border-color: var(--k2-border-strong);
            --bs-btn-disabled-color: var(--k2-ink-3); --bs-btn-disabled-bg: var(--k2-surface); --bs-btn-disabled-border-color: var(--k2-border);
        }
        .btn-secondary, .btn-success {
            --bs-btn-bg: var(--k2-teal); --bs-btn-border-color: var(--k2-teal);
            --bs-btn-hover-bg: #41957D; --bs-btn-hover-border-color: #41957D;
            --bs-btn-active-bg: #357E69; --bs-btn-active-border-color: #357E69;
            --bs-btn-disabled-bg: var(--k2-teal); --bs-btn-disabled-border-color: var(--k2-teal);
        }
        .btn-outline-success, .btn-outline-secondary.text-success {
            --bs-btn-color: var(--k2-teal-text); --bs-btn-border-color: rgba(var(--k2-teal-rgb), .45); --bs-btn-bg: var(--k2-surface);
            --bs-btn-hover-bg: var(--k2-teal-50); --bs-btn-hover-color: var(--k2-teal-text); --bs-btn-hover-border-color: rgba(var(--k2-teal-rgb), .6);
            --bs-btn-active-bg: var(--k2-teal-50); --bs-btn-active-color: var(--k2-teal-text); --bs-btn-active-border-color: var(--k2-teal);
        }
        .btn-warning {
            --bs-btn-bg: var(--k2-amber); --bs-btn-border-color: var(--k2-amber); --bs-btn-color: #fff;
            --bs-btn-hover-bg: var(--k2-amber-600); --bs-btn-hover-border-color: var(--k2-amber-600); --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: var(--k2-amber-600); --bs-btn-active-border-color: var(--k2-amber-600); --bs-btn-active-color: #fff;
        }
        .btn-outline-warning {
            --bs-btn-color: var(--k2-amber-text); --bs-btn-border-color: rgba(var(--k2-amber-rgb), .6); --bs-btn-bg: var(--k2-surface);
            --bs-btn-hover-bg: var(--k2-amber-50); --bs-btn-hover-color: var(--k2-amber-text); --bs-btn-hover-border-color: var(--k2-amber);
            --bs-btn-active-bg: var(--k2-amber-50); --bs-btn-active-color: var(--k2-amber-text); --bs-btn-active-border-color: var(--k2-amber);
        }
        .btn-danger {
            --bs-btn-bg: var(--k2-danger); --bs-btn-border-color: var(--k2-danger);
            --bs-btn-hover-bg: #B8353F; --bs-btn-hover-border-color: #B8353F;
            --bs-btn-active-bg: #9E2B34; --bs-btn-active-border-color: #9E2B34;
        }
        .btn-outline-danger {
            --bs-btn-color: var(--k2-danger-text); --bs-btn-border-color: rgba(var(--k2-danger-rgb), .45); --bs-btn-bg: var(--k2-surface);
            --bs-btn-hover-bg: var(--k2-danger-50); --bs-btn-hover-color: var(--k2-danger-text); --bs-btn-hover-border-color: var(--k2-danger);
            --bs-btn-active-bg: var(--k2-danger-50); --bs-btn-active-color: var(--k2-danger-text); --bs-btn-active-border-color: var(--k2-danger);
        }
        .btn-info, .btn-outline-info {
            --bs-btn-color: var(--k2-primary-text); --bs-btn-bg: var(--k2-primary-50); --bs-btn-border-color: var(--k2-primary-100);
            --bs-btn-hover-bg: var(--k2-primary-100); --bs-btn-hover-color: var(--k2-primary-text); --bs-btn-hover-border-color: var(--k2-primary-100);
            --bs-btn-active-bg: var(--k2-primary-100); --bs-btn-active-color: var(--k2-primary-text); --bs-btn-active-border-color: var(--k2-primary-100);
        }
        .btn-link { --bs-btn-color: var(--k2-primary-text); --bs-btn-hover-color: var(--k2-primary-600); text-decoration: none; }
        .btn-link:hover { transform: none; text-decoration: underline; }
        .btn-close:focus { box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .18); }

        /* Formularios */
        .form-control, .form-select {
            border-color: var(--k2-border);
            border-radius: var(--k2-radius-sm);
            background-color: var(--k2-surface);
            color: var(--k2-ink);
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:hover, .form-select:hover { border-color: var(--k2-border-strong); }
        .form-control:focus, .form-select:focus {
            border-color: var(--k2-primary);
            background-color: var(--k2-surface);
            color: var(--k2-ink);
            box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .15);
        }
        .form-control::placeholder { color: var(--k2-ink-3); }
        .form-control:disabled, .form-control[readonly], .form-select:disabled { background-color: var(--k2-surface-2); color: var(--k2-ink-2); }
        .form-control-sm, .form-select-sm { border-radius: 8px; }
        .form-label { font-weight: 600; font-size: .8rem; color: var(--k2-ink-2); margin-bottom: .3rem; }
        .form-text { color: var(--k2-ink-3); }
        .form-check-input { border-color: var(--k2-border-strong); background-color: var(--k2-surface); }
        .form-check-input:checked { background-color: var(--k2-primary); border-color: var(--k2-primary); }
        .form-check-input:focus { border-color: var(--k2-primary); box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .18); }
        .form-switch .form-check-input:checked { background-color: var(--k2-teal); border-color: var(--k2-teal); }
        .input-group-text { background: var(--k2-surface-2); border-color: var(--k2-border); color: var(--k2-ink-2); border-radius: var(--k2-radius-sm); }
        .input-group-sm > .input-group-text { border-radius: 8px; }
        .form-control.is-invalid, .form-select.is-invalid { border-color: var(--k2-danger); }
        .form-control.is-invalid:focus, .form-select.is-invalid:focus { box-shadow: 0 0 0 3px rgba(var(--k2-danger-rgb), .15); }
        .invalid-feedback { color: var(--k2-danger-text); }
        .form-range::-webkit-slider-thumb { background: var(--k2-primary); }

        /* Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            border-color: var(--k2-border);
            border-radius: var(--k2-radius-sm);
            background: var(--k2-surface);
            color: var(--k2-ink);
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: var(--k2-primary);
            box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .15);
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder { color: var(--k2-ink); }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder { color: var(--k2-ink-3); }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: var(--k2-border);
            border-radius: 12px;
            box-shadow: var(--k2-shadow-lg);
            background: var(--k2-surface);
            color: var(--k2-ink);
            overflow: hidden;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--highlighted {
            background: var(--k2-primary-50);
            color: var(--k2-primary-text);
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--selected,
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option[aria-selected=true]:not(.select2-results__option--highlighted) {
            background: var(--k2-primary-100);
            color: var(--k2-primary-text);
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
            border-color: var(--k2-border);
            border-radius: 8px;
            background: var(--k2-surface);
            color: var(--k2-ink);
        }
        .select2-container--default .select2-selection--single {
            border-color: var(--k2-border) !important;
            border-radius: var(--k2-radius-sm) !important;
            background: var(--k2-surface);
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--k2-ink); }
        .select2-container--default .select2-dropdown { border-color: var(--k2-border); border-radius: 12px; background: var(--k2-surface); color: var(--k2-ink); box-shadow: var(--k2-shadow-lg); overflow: hidden; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--k2-primary-50); color: var(--k2-primary-text); }
        .select2-container--default .select2-search--dropdown .select2-search__field { border-color: var(--k2-border); border-radius: 8px; background: var(--k2-surface); color: var(--k2-ink); }

        /* Tablas */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--k2-ink);
            --bs-table-border-color: var(--k2-border);
            --bs-table-striped-bg: var(--k2-surface-2);
            --bs-table-striped-color: var(--k2-ink);
            --bs-table-hover-bg: var(--k2-surface-2);
            --bs-table-hover-color: var(--k2-ink);
            --bs-table-active-bg: var(--k2-primary-50);
            font-size: .84rem;
        }
        .table > thead > tr > th, .table > :not(caption) > * > th {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--k2-ink-3);
            background: var(--k2-surface-2);
            border-bottom: 1px solid var(--k2-border);
            white-space: nowrap;
            padding: .6rem .75rem;
        }
        .table > tbody > tr > td { padding: .65rem .75rem; vertical-align: middle; }
        .table-sm > tbody > tr > td { padding: .45rem .6rem; }
        .table-sm > thead > tr > th { padding: .5rem .6rem; }
        .table > tbody > tr > th { background: transparent; color: var(--k2-ink); text-transform: none; letter-spacing: 0; font-size: inherit; }
        .table-light, .table > thead.table-light > tr > th { --bs-table-bg: var(--k2-surface-2); --bs-table-color: var(--k2-ink-3); --bs-table-border-color: var(--k2-border); }
        .table-success   { --bs-table-bg: var(--k2-teal-50);    --bs-table-color: var(--k2-ink); --bs-table-border-color: var(--k2-border); }
        .table-danger    { --bs-table-bg: var(--k2-danger-50);  --bs-table-color: var(--k2-ink); --bs-table-border-color: var(--k2-border); }
        .table-warning   { --bs-table-bg: var(--k2-amber-50);   --bs-table-color: var(--k2-ink); --bs-table-border-color: var(--k2-border); }
        .table-info, .table-primary, .table-active { --bs-table-bg: var(--k2-primary-50); --bs-table-color: var(--k2-ink); --bs-table-border-color: var(--k2-border); }
        .table-secondary { --bs-table-bg: var(--k2-surface-2);  --bs-table-color: var(--k2-ink); --bs-table-border-color: var(--k2-border); }
        .table-borderless > :not(caption) > * > * { border-bottom-width: 0; }
        .table-responsive { border-radius: 0 0 var(--k2-radius) var(--k2-radius); }

        /* Badges */
        .badge { font-weight: 700; font-size: .68rem; letter-spacing: .02em; padding: .32em .62em; border-radius: 999px; }
        .badge.bg-primary, .badge.text-bg-primary, .badge.bg-info, .badge.text-bg-info { background: var(--k2-primary-50) !important; color: var(--k2-primary-text) !important; }
        .badge.bg-success, .badge.text-bg-success { background: var(--k2-teal-50) !important; color: var(--k2-teal-text) !important; }
        .badge.bg-warning, .badge.text-bg-warning { background: var(--k2-amber-50) !important; color: var(--k2-amber-text) !important; }
        .badge.bg-danger, .badge.text-bg-danger { background: var(--k2-danger-50) !important; color: var(--k2-danger-text) !important; }
        .badge.bg-secondary, .badge.text-bg-secondary, .badge.bg-light, .badge.text-bg-light, .badge.bg-dark, .badge.text-bg-dark {
            background: var(--k2-surface-2) !important; color: var(--k2-ink-2) !important; box-shadow: inset 0 0 0 1px var(--k2-border);
        }
        .badge.bg-danger.position-absolute, .badge.rounded-pill.bg-danger.position-absolute { background: var(--k2-danger) !important; color: #fff !important; }

        /* Alertas de las vistas */
        .alert { border-radius: var(--k2-radius-sm); border-width: 1px; font-size: .85rem; }
        .alert-success   { --bs-alert-bg: var(--k2-teal-50);    --bs-alert-border-color: rgba(var(--k2-teal-rgb), .35);   --bs-alert-color: var(--k2-teal-text); --bs-alert-link-color: var(--k2-teal-text); }
        .alert-warning   { --bs-alert-bg: var(--k2-amber-50);   --bs-alert-border-color: rgba(var(--k2-amber-rgb), .45);  --bs-alert-color: var(--k2-amber-text); --bs-alert-link-color: var(--k2-amber-text); }
        .alert-danger    { --bs-alert-bg: var(--k2-danger-50);  --bs-alert-border-color: rgba(var(--k2-danger-rgb), .35); --bs-alert-color: var(--k2-danger-text); --bs-alert-link-color: var(--k2-danger-text); }
        .alert-info, .alert-primary { --bs-alert-bg: var(--k2-primary-50); --bs-alert-border-color: var(--k2-primary-100); --bs-alert-color: var(--k2-primary-text); --bs-alert-link-color: var(--k2-primary-text); }
        .alert-secondary, .alert-light { --bs-alert-bg: var(--k2-surface-2); --bs-alert-border-color: var(--k2-border); --bs-alert-color: var(--k2-ink-2); }

        /* Modales */
        .modal-content {
            background: var(--k2-surface);
            border: 1px solid var(--k2-border);
            border-radius: 16px;
            box-shadow: var(--k2-shadow-lg);
            color: var(--k2-ink);
        }
        .modal-header { border-bottom: 1px solid var(--k2-border); padding: 1rem 1.25rem; }
        .modal-title { font-family: var(--k2-font-display); font-weight: 700; font-size: 1rem; }
        .modal-footer { border-top: 1px solid var(--k2-border); background: var(--k2-surface-2); border-radius: 0 0 16px 16px; }
        .modal-backdrop { --bs-backdrop-opacity: .45; }

        /* Pestañas */
        .nav-tabs { border-bottom: 1px solid var(--k2-border); gap: .25rem; }
        .nav-tabs .nav-link {
            border: 0;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            color: var(--k2-ink-2);
            font-weight: 600;
            font-size: .85rem;
            padding: .6rem .9rem;
            border-radius: 8px 8px 0 0;
        }
        .nav-tabs .nav-link:hover { color: var(--k2-primary-text); background: var(--k2-primary-50); border-color: transparent; }
        .nav-tabs .nav-link.active { color: var(--k2-primary-text); background: transparent; border-bottom-color: var(--k2-primary); }
        .nav-pills { --bs-nav-pills-link-active-bg: var(--k2-primary); --bs-nav-pills-border-radius: 10px; }
        .nav-pills .nav-link { font-weight: 600; color: var(--k2-ink-2); }

        /* Paginación */
        .pagination {
            --bs-pagination-bg: var(--k2-surface);
            --bs-pagination-color: var(--k2-ink-2);
            --bs-pagination-border-color: var(--k2-border);
            --bs-pagination-hover-bg: var(--k2-primary-50);
            --bs-pagination-hover-color: var(--k2-primary-text);
            --bs-pagination-hover-border-color: var(--k2-border);
            --bs-pagination-focus-bg: var(--k2-primary-50);
            --bs-pagination-focus-color: var(--k2-primary-text);
            --bs-pagination-focus-box-shadow: 0 0 0 3px rgba(var(--k2-primary-rgb), .15);
            --bs-pagination-active-bg: var(--k2-primary);
            --bs-pagination-active-border-color: var(--k2-primary);
            --bs-pagination-disabled-bg: var(--k2-surface-2);
            --bs-pagination-disabled-color: var(--k2-ink-3);
            --bs-pagination-disabled-border-color: var(--k2-border);
            gap: .2rem;
        }
        .pagination .page-link { border-radius: 8px; font-size: .82rem; font-weight: 600; }

        /* Dropdowns de las vistas */
        .dropdown-menu {
            --bs-dropdown-bg: var(--k2-surface);
            --bs-dropdown-color: var(--k2-ink);
            --bs-dropdown-border-color: var(--k2-border);
            --bs-dropdown-border-radius: 12px;
            --bs-dropdown-padding-y: .4rem;
            --bs-dropdown-padding-x: .4rem;
            --bs-dropdown-link-color: var(--k2-ink);
            --bs-dropdown-link-hover-bg: var(--k2-primary-50);
            --bs-dropdown-link-hover-color: var(--k2-primary-text);
            --bs-dropdown-link-active-bg: var(--k2-primary-50);
            --bs-dropdown-link-active-color: var(--k2-primary-text);
            --bs-dropdown-font-size: .84rem;
            --bs-dropdown-divider-bg: var(--k2-border);
            box-shadow: var(--k2-shadow-lg);
        }
        .dropdown-item { border-radius: 8px; font-weight: 500; }
        .dropdown-header { color: var(--k2-ink-3); font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }

        /* Otros */
        .list-group {
            --bs-list-group-bg: var(--k2-surface);
            --bs-list-group-color: var(--k2-ink);
            --bs-list-group-border-color: var(--k2-border);
            --bs-list-group-border-radius: var(--k2-radius-sm);
            --bs-list-group-action-hover-bg: var(--k2-surface-2);
            --bs-list-group-action-hover-color: var(--k2-ink);
            --bs-list-group-active-bg: var(--k2-primary);
            --bs-list-group-active-border-color: var(--k2-primary);
        }
        .progress { --bs-progress-bg: var(--k2-surface-2); --bs-progress-bar-bg: var(--k2-primary); border-radius: 999px; }
        .accordion {
            --bs-accordion-bg: var(--k2-surface);
            --bs-accordion-color: var(--k2-ink);
            --bs-accordion-border-color: var(--k2-border);
            --bs-accordion-border-radius: var(--k2-radius);
            --bs-accordion-inner-border-radius: calc(var(--k2-radius) - 1px);
            --bs-accordion-btn-bg: var(--k2-surface);
            --bs-accordion-btn-color: var(--k2-ink);
            --bs-accordion-active-bg: var(--k2-primary-50);
            --bs-accordion-active-color: var(--k2-primary-text);
            --bs-accordion-btn-focus-box-shadow: none;
        }
        .breadcrumb { --bs-breadcrumb-divider-color: var(--k2-ink-3); font-size: .8rem; }
        .toast { background: var(--k2-surface); border-color: var(--k2-border); color: var(--k2-ink); }
        .offcanvas { --bs-offcanvas-bg: var(--k2-surface); --bs-offcanvas-color: var(--k2-ink); }
        .popover { --bs-popover-bg: var(--k2-surface); --bs-popover-border-color: var(--k2-border); --bs-popover-body-color: var(--k2-ink); }
        dl.row dt { font-weight: 600; color: var(--k2-ink-3); font-size: .78rem; }
        dl.row dd { color: var(--k2-ink); }
        code { color: var(--k2-danger-text); }
        pre { background: var(--k2-surface-2); border: 1px solid var(--k2-border); border-radius: var(--k2-radius-sm); padding: .75rem 1rem; color: var(--k2-ink); }

        /* Utilidades de color que las vistas usan sobre iconos y textos */
        .text-muted     { color: var(--k2-ink-3) !important; }
        .text-secondary { color: var(--k2-ink-2) !important; }
        .text-dark      { color: var(--k2-ink) !important; }
        .text-body      { color: var(--k2-ink) !important; }
        .text-primary   { color: var(--k2-primary-text) !important; }
        .text-info      { color: var(--k2-primary-text) !important; }
        .text-success   { color: var(--k2-teal) !important; }
        .text-warning   { color: var(--k2-amber-600) !important; }
        .text-danger    { color: var(--k2-danger) !important; }
        .bg-light       { background-color: var(--k2-surface-2) !important; }
        .bg-white       { background-color: var(--k2-surface) !important; }
        .bg-body, .bg-body-tertiary { background-color: var(--k2-surface-2) !important; }
        .border, .border-top, .border-bottom, .border-start, .border-end { border-color: var(--k2-border) !important; }
        .shadow-sm { box-shadow: var(--k2-shadow-sm) !important; }
        .shadow, .shadow-lg { box-shadow: var(--k2-shadow-lg) !important; }
        .rounded { border-radius: var(--k2-radius-sm) !important; }
        .rounded-3 { border-radius: var(--k2-radius) !important; }

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
            .k2-page-title { font-size: 1.1rem; }
            .k2-table th, .k2-table td { padding-left: .9rem; padding-right: .9rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    $k2User    = auth()->user();
    $k2EsAdmin = $k2User->permisos()->administrador()->tieneTodosLosPermisos();
    $k2InstId  = (int) session('institucion_activa_id', 0);
    $k2Inst    = $k2InstId ? \App\Models\Institucion::find($k2InstId) : null;
    $k2SoloIds = $k2EsAdmin
        ? null
        : $k2User->rolesInstitucion()->vigente()->pluck('id_institucion')->map(fn ($v) => (int) $v)->toArray();
    $k2ListaInst = \App\Models\Institucion::listaJerarquica($k2SoloIds);

    // Rol que se muestra bajo el nombre del usuario
    if ($k2EsAdmin) {
        $k2Rol = 'Administrador General';
    } else {
        $k2Rol = $k2InstId
            ? ($k2User->rolesVigentesEnInstitucion($k2InstId)->first()?->rolInstitucion?->nombre ?? 'Usuario')
            : 'Usuario';
    }

    // Avisos: pendientes que requieren intervención del usuario
    $k2Notif = [];
    if ($k2EsAdmin || $k2User->permisos()->licencias()->read()) {
        $k2LicQuery = \App\Models\Licencia::enEstado('pendiente');
        if (! $k2EsAdmin && $k2InstId) {
            $k2LicQuery->whereHas('usuario.designaciones', fn ($q) => $q->vigente()->porInstitucion($k2InstId));
        }
        $k2Notif[] = [
            'label'   => 'Licencias pendientes de aprobación',
            'count'   => $k2LicQuery->count(),
            'icon'    => 'bi-calendar-check',
            'url'     => route('licencias.index', ['estado' => 'pendiente']),
            'variant' => 'amber',
        ];
    }
    $k2Notif[] = [
        'label'   => 'Tickets con novedades',
        'count'   => \App\Models\Ticket::contarNoLeidosParaUsuario($k2User),
        'icon'    => 'bi-life-preserver',
        'url'     => route('tickets.index'),
        'variant' => 'primary',
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
            'label'  => 'Inicio',
            'icon'   => 'bi-house-door-fill',
            'url'    => route('home'),
            'active' => ['home', 'home.*'],
        ],
        [
            'label'  => 'Personal',
            'icon'   => 'bi-people-fill',
            'active' => ['usuarios.*', 'designaciones.*', 'ddjj.*', 'licencias.*', 'avisos.*', 'marcas.*', 'banco-horas.*'],
            'cols'   => 2,
            'items'  => array_values(array_filter([
                ['label' => 'Usuarios',            'desc' => 'Altas, datos y roles',           'icon' => 'bi-person-badge',      'url' => route('usuarios.index'),      'active' => ['usuarios.*'], 'activo' => request()->routeIs('usuarios.*') && ! request()->boolean('todos')],
                $k2EsAdmin ? ['label' => 'Todos los usuarios', 'desc' => 'Sin filtrar por institución', 'icon' => 'bi-people',  'url' => route('usuarios.index', ['todos' => 1]), 'active' => [], 'activo' => request()->routeIs('usuarios.index') && request()->boolean('todos')] : null,
                ['label' => 'Designaciones',       'desc' => 'Cargos y dependencias',          'icon' => 'bi-briefcase',         'url' => route('designaciones.index'), 'active' => ['designaciones.*']],
                ['label' => 'DDJJ',                'desc' => 'Declaraciones de horario',       'icon' => 'bi-file-earmark-text', 'url' => route('ddjj.index'),          'active' => ['ddjj.*']],
                ['label' => 'Licencias y permisos','desc' => 'Solicitudes y aprobaciones',     'icon' => 'bi-calendar-check',    'url' => route('licencias.index'),     'active' => ['licencias.*']],
                ['label' => 'Avisos del personal', 'desc' => 'Novedades comunicadas',          'icon' => 'bi-megaphone',         'url' => route('avisos.index'),        'active' => ['avisos.*']],
                ['label' => 'Marcas',              'desc' => 'Registros de fichaje',           'icon' => 'bi-fingerprint',       'url' => route('marcas.index'),        'active' => ['marcas.*']],
                ['label' => 'Banco de horas',      'desc' => 'Saldos y ajustes',               'icon' => 'bi-bank',              'url' => route('banco-horas.index'),   'active' => ['banco-horas.*']],
            ])),
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
                ['label' => 'Tickets',                'desc' => 'Mesa de ayuda',              'icon' => 'bi-life-preserver', 'url' => route('tickets.index'),                 'active' => ['tickets.index', 'tickets.show', 'tickets.create'], 'badge' => \App\Models\Ticket::contarNoLeidosParaUsuario($k2User)],
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
    if ($k2Inst) {
        $k2Palette[] = ['label' => 'Dashboard de ' . ($k2Inst->sigla ?: $k2Inst->nombre), 'desc' => 'Resumen de la institución activa', 'icon' => 'bi-building', 'url' => route('home.institucion'), 'group' => 'General', 'external' => false];
    }
    $k2Palette[] = ['label' => 'Mi perfil',         'desc' => 'Datos personales',            'icon' => 'bi-person-circle', 'url' => route('perfil'),                     'group' => 'Cuenta', 'external' => false];
    $k2Palette[] = ['label' => 'Métodos de acceso', 'desc' => 'Contraseña, PIN y Google',    'icon' => 'bi-key',           'url' => route('perfil') . '#metodos-login',  'group' => 'Cuenta', 'external' => false];

    $k2Iniciales = strtoupper(substr($k2User->nombres ?? 'U', 0, 1)) . strtoupper(substr($k2User->apellidos ?? '', 0, 1));
@endphp

{{-- ══ BARRA SUPERIOR ══════════════════════════════════════════════════════ --}}
<header class="k2-topbar">
    <div class="k2-topbar-inner">

        <button class="k2-iconbtn k2-menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#k2MobileNav" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>

        <a href="{{ route('home') }}" class="k2-brand">
            <span class="k2-brand-mark">K</span>
            <span>
                <span class="k2-brand-name">KAIROS</span>
                <span class="k2-brand-sub">Control horario</span>
            </span>
        </a>

        <nav class="k2-navwrap" aria-label="Principal">
            @include('layouts._nav', ['modo' => 'desktop', 'menu' => $k2Menu])
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
                @if(! $k2Inst)
                    <form method="POST" action="{{ route('institucion-activa.cambiar') }}" id="form-inst-auto" class="d-none">
                        @csrf
                        <input type="hidden" name="id_institucion" value="{{ $k2ListaInst[0]['institucion']->id }}">
                    </form>
                    <script>document.getElementById('form-inst-auto').submit();</script>
                @endif
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
                        <span class="k2-user-role">{{ $k2Rol }}</span>
                    </span>
                    <i class="bi bi-chevron-down k2-caret" style="font-size:.6rem;opacity:.6"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end k2-dropdown" style="--bs-dropdown-min-width:250px">
                    <div class="k2-dropdown-head">{{ $k2User->email ?? $k2User->documento }}</div>
                    <a class="k2-dropdown-item {{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}">
                        <span class="k2-dropdown-icon"><i class="bi bi-person-circle"></i></span>
                        <span class="k2-dropdown-text">
                            <span class="k2-dropdown-label">Mi perfil</span>
                            <span class="k2-dropdown-desc">Datos personales</span>
                        </span>
                    </a>
                    <a class="k2-dropdown-item" href="{{ route('perfil') }}#metodos-login">
                        <span class="k2-dropdown-icon"><i class="bi bi-key"></i></span>
                        <span class="k2-dropdown-text">
                            <span class="k2-dropdown-label">Métodos de acceso</span>
                            <span class="k2-dropdown-desc">Contraseña, PIN y Google</span>
                        </span>
                    </a>
                    @if($k2Inst)
                        <a class="k2-dropdown-item {{ request()->routeIs('home.institucion') ? 'active' : '' }}" href="{{ route('home.institucion') }}">
                            <span class="k2-dropdown-icon"><i class="bi bi-building"></i></span>
                            <span class="k2-dropdown-text">
                                <span class="k2-dropdown-label">Dashboard institucional</span>
                                <span class="k2-dropdown-desc">{{ $k2Inst->nombre }}</span>
                            </span>
                        </a>
                    @endif
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
        <a href="{{ route('home') }}" class="k2-brand" id="k2MobileNavLabel">
            <span class="k2-brand-mark">K</span>
            <span><span class="k2-brand-name">KAIROS</span></span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0 pb-4">
        @include('layouts._nav', ['modo' => 'mobile', 'menu' => $k2Menu, 'instActiva' => $k2Inst])
    </div>
</div>

{{-- ══ ENCABEZADO DE PÁGINA ════════════════════════════════════════════════ --}}
<div class="k2-pagehead">
    <div>
        <ol class="k2-crumbs">
            <li><a href="{{ route('home') }}" title="Inicio"><i class="bi bi-house-door"></i></a></li>
            @yield('breadcrumb')
        </ol>
        @hasSection('page-title')
            <div class="k2-pagehead-titles">
                <h1 class="k2-title">@yield('page-title')</h1>
                @hasSection('page-subtitle')
                    <p class="k2-subtitle">@yield('page-subtitle')</p>
                @endif
            </div>
        @endif
    </div>
    @hasSection('page-actions')
        <div class="k2-page-actions">@yield('page-actions')</div>
    @endif
</div>

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

    /* ── Alertas flash del layout: cierre manual y automático ────────── */
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
    /* Alertas Bootstrap que aún renderizan algunas vistas */
    document.querySelectorAll('.alert.alert-dismissible.fade.show').forEach(function (el) {
        setTimeout(function () { bootstrap.Alert.getOrCreateInstance(el).close(); }, 7000);
    });

    /* ── Dropdowns de la barra con posicionamiento fijo (evitan recortes) ─ */
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
        return (s || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
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
