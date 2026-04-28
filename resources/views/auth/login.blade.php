<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairos — Iniciar Sesión</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════
           VARIABLES Y RESET
        ══════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --azul-marino:  #0f2044;
            --azul-medio:   #1a3a6b;
            --azul-claro:   #2a5298;
            --dorado:       #c9a227;
            --dorado-claro: #e8c547;
            --blanco:       #ffffff;
            --gris-fondo:   #f7f8fc;
            --gris-borde:   #e2e8f0;
            --texto:        #2d3748;
            --texto-suave:  #718096;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        /* ══════════════════════════════════════
           ANIMACIÓN ENTRADA
        ══════════════════════════════════════ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
            animation: fadeUp 0.55s cubic-bezier(.22,.68,0,1.2) both;
        }

        /* ══════════════════════════════════════
           PANEL IZQUIERDO
        ══════════════════════════════════════ */
        .panel-izquierdo {
            position: relative;
            width: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* — Slides — */
        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1.3s ease;
        }

        .slide.activo { opacity: 1; }

        /* Fondos visuales texturizados con gradientes institucionales */
        .slide-1 {
            background:
                linear-gradient(150deg, rgba(15,32,68,.88) 0%, rgba(201,162,39,.45) 100%),
                repeating-linear-gradient(45deg,  #0d1c3a 0,#0d1c3a 1px, #102247 1px, #102247 18px),
                repeating-linear-gradient(-45deg, #0f2044 0,#0f2044 1px, #1a3a6b 1px, #1a3a6b 18px);
        }
        .slide-2 {
            background:
                linear-gradient(200deg, rgba(26,58,107,.9) 0%, rgba(15,32,68,.75) 55%, rgba(201,162,39,.4) 100%),
                repeating-conic-gradient(#102247 0% 25%, #0f2044 0% 50%) 0 0 / 24px 24px;
        }
        .slide-3 {
            background:
                linear-gradient(120deg, rgba(201,162,39,.35) 0%, rgba(15,32,68,.94) 50%, rgba(26,58,107,.85) 100%),
                repeating-linear-gradient(30deg, #0c1a38 0,#0c1a38 1px, #0f2044 1px, #0f2044 16px);
        }

        /* Velo inferior para legibilidad */
        .slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                rgba(15,32,68,.25)  0%,
                rgba(15,32,68,.05) 45%,
                rgba(15,32,68,.65) 100%
            );
        }

        /* — Contenido sobre los slides — */
        .panel-izq-contenido {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.5rem 2.75rem;
            color: #fff;
        }

        /* Logo */
        .logo-kairos {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .logo-icono {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            color: var(--azul-marino);
            box-shadow: 0 4px 18px rgba(201,162,39,.45);
            flex-shrink: 0;
        }

        .logo-textos { display: flex; flex-direction: column; }

        .logo-nombre {
            font-family: 'Playfair Display', serif;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: 4px;
            line-height: 1;
        }

        .logo-subtitulo {
            font-size: .6rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--dorado-claro);
            margin-top: 2px;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .stat-card {
            text-align: center;
            padding: 1.4rem .8rem 1.1rem;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 14px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: transform .3s, background .3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            background: rgba(255,255,255,.12);
        }

        .stat-numero {
            font-family: 'Playfair Display', serif;
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--dorado-claro);
            line-height: 1;
        }

        .stat-icono {
            font-size: .9rem;
            color: rgba(255,255,255,.55);
            margin-bottom: .35rem;
        }

        .stat-etiqueta {
            font-size: .65rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(255,255,255,.65);
            margin-top: .4rem;
        }

        /* Badge institución */
        .badge-inst {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(201,162,39,.4);
            border-radius: 50px;
            padding: .55rem 1.1rem;
            font-size: .72rem;
            letter-spacing: .4px;
            backdrop-filter: blur(10px);
            width: fit-content;
        }

        .badge-inst i { color: var(--dorado); }

        /* Dots slideshow */
        .slide-dots {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 7px;
            z-index: 10;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 3px;
            background: rgba(255,255,255,.35);
            transition: all .35s ease;
            cursor: pointer;
            border: none;
            padding: 0;
        }

        .dot.activo {
            width: 22px;
            background: var(--dorado);
        }

        /* ══════════════════════════════════════
           PANEL DERECHO
        ══════════════════════════════════════ */
        .panel-derecho {
            width: 50%;
            background: var(--blanco);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 2.5rem;
            overflow-y: auto;
        }

        .caja-form {
            width: 100%;
            max-width: 390px;
        }

        /* Cabecera del form */
        .titulo-form {
            font-family: 'Playfair Display', serif;
            font-size: 2.05rem;
            font-weight: 700;
            color: var(--azul-marino);
            line-height: 1.1;
            margin-bottom: .3rem;
        }

        .subtitulo-form {
            font-size: .86rem;
            color: var(--texto-suave);
            margin-bottom: 1.85rem;
        }

        /* Campos con icono flotante */
        .campo-icono {
            position: relative;
            margin-bottom: 1.1rem;
        }

        .campo-icono .icono-prefijo {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0bec5;
            font-size: .95rem;
            z-index: 4;
            pointer-events: none;
            transition: color .2s;
        }

        .campo-icono:focus-within .icono-prefijo {
            color: var(--azul-medio);
        }

        .campo-icono .form-control {
            height: 50px;
            padding-left: 2.65rem;
            padding-right: 2.65rem;
            border: 1.5px solid var(--gris-borde);
            border-radius: 11px;
            font-size: .88rem;
            color: var(--texto);
            background: var(--gris-fondo);
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .campo-icono .form-control:focus {
            border-color: var(--azul-medio);
            box-shadow: 0 0 0 3.5px rgba(26,58,107,.1);
            background: var(--blanco);
        }

        .campo-icono .form-control.is-invalid {
            border-color: #e53e3e;
            background: #fff5f5;
        }

        .campo-icono .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(229,62,62,.15);
        }

        /* Toggle password */
        .btn-ojo {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b0bec5;
            cursor: pointer;
            z-index: 4;
            font-size: .95rem;
            padding: 3px 5px;
            line-height: 1;
            transition: color .2s;
        }

        .btn-ojo:hover { color: var(--azul-medio); }

        .invalid-feedback { font-size: .76rem; margin-top: 4px; }

        /* Botón ingresar */
        .btn-ingresar {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--azul-marino) 0%, var(--azul-claro) 100%);
            border: none;
            border-radius: 11px;
            color: #fff;
            font-size: .92rem;
            font-weight: 600;
            letter-spacing: .4px;
            margin-top: .6rem;
            transition: opacity .2s, transform .2s, box-shadow .2s;
            box-shadow: 0 4px 18px rgba(15,32,68,.28);
            cursor: pointer;
        }

        .btn-ingresar:hover:not(:disabled) {
            opacity: .93;
            transform: translateY(-1px);
            box-shadow: 0 7px 22px rgba(15,32,68,.38);
        }

        .btn-ingresar:active:not(:disabled) { transform: translateY(0); }

        .btn-ingresar:disabled { opacity: .68; cursor: not-allowed; transform: none; }

        /* Separador */
        .separador {
            display: flex;
            align-items: center;
            gap: .9rem;
            margin: 1.4rem 0;
        }

        .separador::before,
        .separador::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gris-borde);
        }

        .separador span {
            font-size: .75rem;
            color: #b0bec5;
            white-space: nowrap;
        }

        /* Botón Google */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .7rem;
            width: 100%;
            height: 50px;
            background: var(--blanco);
            border: 1.5px solid var(--gris-borde);
            border-radius: 11px;
            color: var(--texto);
            font-size: .88rem;
            font-weight: 500;
            text-decoration: none;
            transition: background .2s, border-color .2s, box-shadow .2s, transform .2s;
        }

        .btn-google:hover {
            background: #fafbff;
            border-color: #cbd5e0;
            box-shadow: 0 3px 10px rgba(0,0,0,.07);
            transform: translateY(-1px);
            color: var(--texto);
        }

        .google-logo { width: 20px; height: 20px; flex-shrink: 0; }

        /* Link SSO */
        .link-sso {
            display: block;
            text-align: center;
            margin-top: .65rem;
            font-size: .78rem;
            color: var(--texto-suave);
            text-decoration: none;
            transition: color .2s;
        }

        .link-sso:hover { color: var(--azul-medio); }

        /* Indicador sistema operativo */
        .indicador-sistema {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-top: 1.8rem;
            font-size: .72rem;
            color: #b0bec5;
        }

        .pulso {
            position: relative;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #48bb78;
            flex-shrink: 0;
        }

        .pulso::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: rgba(72,187,120,.35);
            animation: pulsando 1.9s ease-in-out infinite;
        }

        @keyframes pulsando {
            0%, 100% { transform: scale(1);   opacity: .7; }
            50%       { transform: scale(1.6); opacity: 0;  }
        }

        /* ══════════════════════════════════════
           SHAKE EN ERROR
        ══════════════════════════════════════ */
        @keyframes shake {
            0%, 100% { transform: translateX(0);  }
            16%       { transform: translateX(-9px); }
            33%       { transform: translateX(8px);  }
            50%       { transform: translateX(-6px); }
            67%       { transform: translateX(5px);  }
            84%       { transform: translateX(-3px); }
        }

        .shake { animation: shake 0.52s ease; }

        /* ══════════════════════════════════════
           RESPONSIVE MOBILE
        ══════════════════════════════════════ */
        @media (max-width: 768px) {
            html, body { overflow: auto; }

            .login-wrapper {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }

            .panel-izquierdo {
                width: 100%;
                height: 30vh;
                min-height: 180px;
                flex-shrink: 0;
            }

            .stats-grid { display: none; }

            .panel-izq-contenido { padding: 1.5rem 2rem; }

            .logo-nombre { font-size: 1.3rem; }

            .panel-derecho {
                width: 100%;
                padding: 2rem 1.5rem 3rem;
                align-items: flex-start;
            }

            .slide-dots { bottom: 1rem; }
        }

        @media (max-width: 400px) {
            .panel-derecho { padding: 1.5rem 1rem 2rem; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    {{-- ══════════════ PANEL IZQUIERDO ══════════════ --}}
    <div class="panel-izquierdo">

        <div class="slide slide-1 activo"></div>
        <div class="slide slide-2"></div>
        <div class="slide slide-3"></div>

        <div class="panel-izq-contenido">

            {{-- Logo --}}
            <div class="logo-kairos">
                <div class="logo-icono">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <div class="logo-textos">
                    <span class="logo-nombre">KAIROS</span>
                    <span class="logo-subtitulo">Control Horario Institucional</span>
                </div>
            </div>

            {{-- Estadísticas --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icono"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-numero" data-target="1247" data-sufijo="">0</div>
                    <div class="stat-etiqueta">Personal</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icono"><i class="bi bi-diagram-3-fill"></i></div>
                    <div class="stat-numero" data-target="48" data-sufijo="">0</div>
                    <div class="stat-etiqueta">Dependencias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icono"><i class="bi bi-activity"></i></div>
                    <div class="stat-numero" data-target="98" data-sufijo="%">0</div>
                    <div class="stat-etiqueta">Disponibilidad</div>
                </div>
            </div>

            {{-- Badge institución --}}
            <div class="badge-inst">
                <i class="bi bi-building-fill"></i>
                Universidad Nacional de San Juan
            </div>

        </div>

        {{-- Dots navegación --}}
        <div class="slide-dots">
            <button class="dot activo" data-idx="0" aria-label="Slide 1"></button>
            <button class="dot"        data-idx="1" aria-label="Slide 2"></button>
            <button class="dot"        data-idx="2" aria-label="Slide 3"></button>
        </div>

    </div>

    {{-- ══════════════ PANEL DERECHO ══════════════ --}}
    <div class="panel-derecho">
        <div class="caja-form" id="cajaForm">

            <h1 class="titulo-form">Bienvenido</h1>
            <p class="subtitulo-form">Iniciá sesión en tu cuenta institucional</p>

            {{-- Alerta de errores --}}
            @if ($errors->any())
            <div class="alert d-flex align-items-start gap-2 mb-3 py-2 px-3"
                 role="alert"
                 style="background:#fff5f5; border:1.5px solid #feb2b2; border-radius:10px; font-size:.83rem; color:#c53030;">
                <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="formLogin" novalidate>
                @csrf
                {{-- Campo documento --}}
                <div class="campo-icono">
                    <i class="bi bi-person-vcard icono-prefijo"></i>
                    <input
                        type="text"
                        id="documento"
                        name="documento"
                        class="form-control @error('documento') is-invalid @enderror"
                        placeholder="Número de documento"
                        value="{{ old('documento') }}"
                        autocomplete="username"
                        inputmode="numeric"
                        autofocus
                    >
                    @error('documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo password --}}
                <div class="campo-icono">
                    <i class="bi bi-lock icono-prefijo"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Contraseña"
                        autocomplete="current-password"
                    >
                    <button type="button" id="btnOjo" class="btn-ojo" aria-label="Mostrar contraseña" tabindex="-1">
                        <i class="bi bi-eye" id="iconoOjo"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Recordarme + ¿Olvidaste? --}}
                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.82rem;">
                    <label class="d-flex align-items-center gap-2 text-secondary" style="cursor:pointer;">
                        <input type="checkbox" name="recordar" class="form-check-input m-0" style="width:14px;height:14px;">
                        Recordarme
                    </label>
                    <a href="{{ route('password.request') }}" style="color:var(--azul-medio);text-decoration:none;font-size:.8rem;transition:color .2s;"
                       onmouseover="this.style.color='var(--azul-claro)'" onmouseout="this.style.color='var(--azul-medio)'">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                {{-- Botón ingresar --}}
                <button type="submit" class="btn-ingresar" id="btnIngresar">
                    <span id="textoBtn">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                    </span>
                    <span id="spinnerBtn" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Verificando...
                    </span>
                </button>

            </form>

            {{-- Separador --}}
            <div class="separador">
                <span>o continuar con</span>
            </div>

            {{-- Google --}}
            <a href="{{ route('auth.google') }}" class="btn-google" id="btnGoogle">
                <svg class="google-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Acceder con Google
            </a>

            {{-- SSO --}}
            <a href="#" class="link-sso">
                <i class="bi bi-shield-lock me-1"></i>Acceso SSO via SIU
            </a>

            {{-- Indicador sistema --}}
            <div class="indicador-sistema">
                <div class="pulso"></div>
                <span>Sistema operativo &mdash; Kairos v1.0</span>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── SLIDESHOW ─────────────────────────────── */
    const slides       = document.querySelectorAll('.slide');
    const dots         = document.querySelectorAll('.dot');
    let   idxActual    = 0;
    let   intervalo;

    function irASlide(idx) {
        slides[idxActual].classList.remove('activo');
        dots[idxActual].classList.remove('activo');
        idxActual = (idx + slides.length) % slides.length;
        slides[idxActual].classList.add('activo');
        dots[idxActual].classList.add('activo');
    }

    function iniciarSlideshow() {
        intervalo = setInterval(() => irASlide(idxActual + 1), 5000);
    }

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            clearInterval(intervalo);
            irASlide(parseInt(dot.dataset.idx));
            iniciarSlideshow();
        });
    });

    iniciarSlideshow();

    /* ── TOGGLE PASSWORD ──────────────────────── */
    const inputPass = document.getElementById('password');
    const btnOjo    = document.getElementById('btnOjo');
    const iconoOjo  = document.getElementById('iconoOjo');

    btnOjo.addEventListener('click', function () {
        const visible = inputPass.type === 'text';
        inputPass.type      = visible ? 'password' : 'text';
        iconoOjo.className  = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
        btnOjo.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    /* ── SPINNER AL ENVIAR ────────────────────── */
    const formLogin   = document.getElementById('formLogin');
    const btnIngresar = document.getElementById('btnIngresar');
    const textoBtn    = document.getElementById('textoBtn');
    const spinnerBtn  = document.getElementById('spinnerBtn');

    formLogin.addEventListener('submit', function () {
        textoBtn.classList.add('d-none');
        spinnerBtn.classList.remove('d-none');
        btnIngresar.disabled = true;
    });

    /* ── SHAKE EN ERROR ───────────────────────── */
    @if ($errors->any())
    const cajaForm = document.getElementById('cajaForm');
    cajaForm.classList.add('shake');
    cajaForm.addEventListener('animationend', () => cajaForm.classList.remove('shake'), { once: true });
    @endif

    /* ── CONTADORES ANIMADOS ──────────────────── */
    function animarContador(el) {
        const objetivo = parseInt(el.dataset.target, 10);
        const sufijo   = el.dataset.sufijo || '';
        const duracion = 1600;
        const tInicio  = performance.now();

        (function tick(ahora) {
            const t    = Math.min((ahora - tInicio) / duracion, 1);
            const ease = 1 - Math.pow(1 - t, 4); // easeOutQuart
            el.textContent = Math.floor(ease * objetivo) + sufijo;
            if (t < 1) requestAnimationFrame(tick);
            else el.textContent = objetivo + sufijo;
        })(tInicio);
    }

    const contadores = document.querySelectorAll('.stat-numero[data-target]');

    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    animarContador(e.target);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.4 });
        contadores.forEach(c => obs.observe(c));
    } else {
        contadores.forEach(animarContador);
    }

    /* ── FOCUS → quitar is-invalid al escribir ── */
    document.querySelectorAll('.form-control.is-invalid').forEach(input => {
        input.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        }, { once: true });
    });

    /* ── SOLO DÍGITOS EN DOCUMENTO ────────────── */
    const inputDoc = document.getElementById('documento');
    if (inputDoc) {
        inputDoc.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    }

}());
</script>

</body>
</html>
