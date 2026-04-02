<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairos — Control Horario Institucional</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --azul:    #0f2044;
            --azul-m:  #1a3a6b;
            --azul-c:  #2a5298;
            --dorado:  #c9a227;
            --oro:     #e8c547;
            --blanco:  #ffffff;
            --gris:    #f4f6fb;
            --texto:   #2d3748;
            --suave:   #718096;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--azul);
            color: var(--blanco);
            overflow: hidden;
        }

        /* ── Fondo con textura geométrica sutil ─────── */
        .fondo {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 70% 40%, rgba(42,82,152,.35) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 20% 80%, rgba(201,162,39,.12) 0%, transparent 60%),
                repeating-linear-gradient(
                    55deg,
                    transparent 0px,
                    transparent 38px,
                    rgba(255,255,255,.018) 38px,
                    rgba(255,255,255,.018) 39px
                );
            z-index: 0;
        }

        /* ── Partículas flotantes ────────────────────── */
        .particulas {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
        }

        .p {
            position: absolute;
            border-radius: 50%;
            background: var(--dorado);
            opacity: 0;
            animation: flotar var(--d, 8s) var(--delay, 0s) ease-in-out infinite;
        }

        @keyframes flotar {
            0%   { transform: translateY(0)   scale(1);   opacity: 0;    }
            15%  { opacity: var(--op, .25); }
            85%  { opacity: var(--op, .25); }
            100% { transform: translateY(-110vh) scale(.6); opacity: 0; }
        }

        /* ── Layout principal ────────────────────────── */
        .pagina {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
            padding: 2rem 3rem;
        }

        /* ── Header ──────────────────────────────────── */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: bajar .6s ease both;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none;
            color: inherit;
        }

        .logo-icono {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--dorado), var(--oro));
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--azul);
            box-shadow: 0 3px 14px rgba(201,162,39,.4);
            flex-shrink: 0;
        }

        .logo-texto {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 4px;
            line-height: 1;
        }

        .logo-sub {
            font-size: .55rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--oro);
            display: block;
            margin-top: 2px;
        }

        .btn-acceder-nav {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.35rem;
            border: 1.5px solid rgba(255,255,255,.22);
            border-radius: 50px;
            font-size: .82rem;
            font-weight: 500;
            color: rgba(255,255,255,.88);
            text-decoration: none;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,.07);
            transition: all .25s ease;
            letter-spacing: .3px;
        }

        .btn-acceder-nav:hover {
            background: rgba(255,255,255,.15);
            border-color: rgba(255,255,255,.4);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ── Hero central ────────────────────────────── */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem 1rem;
            gap: 0;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(201,162,39,.15);
            border: 1px solid rgba(201,162,39,.3);
            border-radius: 50px;
            padding: .4rem 1rem;
            font-size: .72rem;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--oro);
            margin-bottom: 1.75rem;
            animation: bajar .7s .1s ease both;
        }

        .hero-badge i { font-size: .8rem; }

        .hero-titulo {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 7vw, 5.5rem);
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: -1px;
            margin-bottom: 1.25rem;
            animation: bajar .7s .2s ease both;
        }

        .hero-titulo span {
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 60%, #fff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: clamp(.9rem, 1.5vw, 1.05rem);
            font-weight: 300;
            color: rgba(255,255,255,.62);
            max-width: 480px;
            line-height: 1.7;
            margin-bottom: 2.75rem;
            animation: bajar .7s .3s ease both;
        }

        /* ── Botón principal ─────────────────────────── */
        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 100%);
            color: var(--azul);
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .4px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 6px 28px rgba(201,162,39,.45);
            transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
            animation: bajar .7s .4s ease both;
        }

        .btn-cta:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 36px rgba(201,162,39,.55);
            filter: brightness(1.05);
            color: var(--azul);
            text-decoration: none;
        }

        .btn-cta:active { transform: translateY(-1px) scale(1); }

        .btn-cta i { font-size: 1rem; transition: transform .25s ease; }
        .btn-cta:hover i { transform: translateX(4px); }

        /* ── Píldoras de características ─────────────── */
        .features {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-top: 2.5rem;
            animation: bajar .7s .5s ease both;
            flex-wrap: wrap;
            justify-content: center;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .75rem;
            color: rgba(255,255,255,.48);
            letter-spacing: .3px;
        }

        .feature i {
            color: var(--dorado);
            font-size: .8rem;
        }

        .feature-sep {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
        }

        /* ── Footer ──────────────────────────────────── */
        footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .72rem;
            color: rgba(255,255,255,.28);
            letter-spacing: .3px;
            animation: subir .6s .6s ease both;
        }

        footer a {
            color: inherit;
            text-decoration: none;
            transition: color .2s;
        }

        footer a:hover { color: rgba(255,255,255,.55); }

        .footer-dots {
            display: flex;
            gap: 5px;
        }

        .footer-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
        }

        .footer-dot.activo {
            background: var(--dorado);
        }

        /* ── Animaciones ──────────────────────────────── */
        @keyframes bajar {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes subir {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 600px) {
            .pagina { padding: 1.5rem; }
            .logo-texto { font-size: 1.1rem; letter-spacing: 3px; }
            .btn-acceder-nav span { display: none; }
            footer { flex-direction: column; gap: .75rem; text-align: center; }
            .features { gap: 1rem; }
            .feature-sep { display: none; }
        }
    </style>
</head>
<body>

<div class="fondo"></div>

<div class="particulas" id="particulas"></div>

<div class="pagina">

    {{-- Header --}}
    <header>
        <div class="logo">
            <div class="logo-icono">
                <i class="bi bi-compass-fill"></i>
            </div>
            <div>
                <span class="logo-texto">KAIROS</span>
                <span class="logo-sub">Control Horario</span>
            </div>
        </div>

        <a href="{{ route('login') }}" class="btn-acceder-nav">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Acceder al sistema</span>
        </a>
    </header>

    {{-- Hero --}}
    <div class="hero">

        <div class="hero-badge">
            <i class="bi bi-shield-check"></i>
            Universidad Nacional de San Juan
        </div>

        <h1 class="hero-titulo">
            Gestión de<br><span>tiempo institucional</span>
        </h1>

        <p class="hero-desc">
            Plataforma centralizada para el control de asistencia, licencias, marcas horarias e informes del personal universitario.
        </p>

        <a href="{{ route('login') }}" class="btn-cta">
            Iniciar sesión
            <i class="bi bi-arrow-right"></i>
        </a>

        <div class="features">
            <div class="feature">
                <i class="bi bi-clock-history"></i>
                Control de marcas
            </div>
            <div class="feature-sep"></div>
            <div class="feature">
                <i class="bi bi-calendar3"></i>
                Licencias y ausencias
            </div>
            <div class="feature-sep"></div>
            <div class="feature">
                <i class="bi bi-bar-chart-line"></i>
                Informes automáticos
            </div>
            <div class="feature-sep"></div>
            <div class="feature">
                <i class="bi bi-diagram-3"></i>
                Multi-institución
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <footer>
        <span>&copy; {{ date('Y') }} UNSJ &mdash; Kairos v1.0</span>
        <div class="footer-dots">
            <div class="footer-dot activo"></div>
            <div class="footer-dot"></div>
            <div class="footer-dot"></div>
        </div>
        <span>
            @auth
                <a href="{{ route('home') }}">Ir al panel</a>
            @else
                <a href="{{ route('login') }}">Iniciar sesión</a>
            @endauth
        </span>
    </footer>

</div>

<script>
(function () {
    // Partículas flotantes
    const contenedor = document.getElementById('particulas');
    const config = [
        { size: 3,  left: '8%',  delay: 0,   dur: 12, op: .18 },
        { size: 5,  left: '18%', delay: 1.5, dur: 16, op: .12 },
        { size: 2,  left: '32%', delay: 3,   dur: 11, op: .22 },
        { size: 4,  left: '48%', delay: .8,  dur: 14, op: .15 },
        { size: 6,  left: '58%', delay: 5,   dur: 18, op: .10 },
        { size: 3,  left: '70%', delay: 2.2, dur: 13, op: .20 },
        { size: 4,  left: '82%', delay: 4,   dur: 15, op: .14 },
        { size: 2,  left: '91%', delay: 1,   dur: 10, op: .25 },
        { size: 5,  left: '24%', delay: 7,   dur: 17, op: .11 },
        { size: 3,  left: '65%', delay: 6,   dur: 12, op: .19 },
    ];

    config.forEach(function (c) {
        const el = document.createElement('div');
        el.className = 'p';
        el.style.cssText = [
            'width:'  + c.size + 'px',
            'height:' + c.size + 'px',
            'left:'   + c.left,
            'bottom: -10px',
            '--d:'    + c.dur   + 's',
            '--delay:'+ c.delay + 's',
            '--op:'   + c.op,
        ].join(';');
        contenedor.appendChild(el);
    });

    // Animación sutil del botón CTA al hacer scroll en mobile
    const cta = document.querySelector('.btn-cta');
    let pulso = null;
    function iniciarPulso() {
        if (pulso) return;
        pulso = setTimeout(function animar() {
            cta.style.transform = 'translateY(-4px) scale(1.015)';
            setTimeout(function () {
                cta.style.transform = '';
                pulso = setTimeout(animar, 3200);
            }, 450);
        }, 2000);
    }
    iniciarPulso();
    cta.addEventListener('mouseenter', function () { clearTimeout(pulso); pulso = null; });
    cta.addEventListener('mouseleave', iniciarPulso);
}());
</script>

</body>
</html>
