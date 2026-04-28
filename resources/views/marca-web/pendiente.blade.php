<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorización pendiente — KAIROS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        :root {
            --azul:   #0f2044;
            --azul-m: #1a3a6b;
            --azul-c: #2a5298;
            --dorado: #c9a227;
            --oro:    #e8c547;
            --blanco: #ffffff;
        }

        body {
            background: var(--azul);
            color: var(--blanco);
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* ── Fondo geométrico ───────────────────────────────────── */
        .fondo {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 70% 35%, rgba(42,82,152,.38) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 20% 80%, rgba(201,162,39,.14) 0%, transparent 60%),
                repeating-linear-gradient(
                    55deg,
                    transparent 0px,
                    transparent 38px,
                    rgba(255,255,255,.018) 38px,
                    rgba(255,255,255,.018) 39px
                );
            z-index: 0;
        }

        /* ── Partículas ─────────────────────────────────────────── */
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
            0%   { transform: translateY(0) scale(1);       opacity: 0; }
            15%  { opacity: var(--op, .2); }
            85%  { opacity: var(--op, .2); }
            100% { transform: translateY(-110vh) scale(.5); opacity: 0; }
        }

        /* ── Contenido ──────────────────────────────────────────── */
        .pagina {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            animation: bajar .6s ease both;
        }
        @keyframes bajar {
            from { opacity: 0; transform: translateY(-14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: .7rem;
        }
        .logo-icono {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--dorado), var(--oro));
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: var(--azul);
            box-shadow: 0 3px 14px rgba(201,162,39,.4);
            flex-shrink: 0;
        }
        .logo-texto {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 4px;
            line-height: 1;
        }
        .logo-sub {
            font-size: .52rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--oro);
            display: block;
            margin-top: 2px;
        }

        /* Card */
        .card {
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 1.25rem;
            padding: 2.75rem 2.75rem 2.25rem;
            width: 100%;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            text-align: center;
        }

        /* Ícono de estado */
        .estado-icono {
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            background: rgba(201,162,39,.13);
            border: 2px solid rgba(201,162,39,.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--oro);
            margin: 0 auto 1.5rem;
            animation: pulso 2.5s ease-in-out infinite;
        }
        @keyframes pulso {
            0%, 100% { box-shadow: 0 0 0 0   rgba(201,162,39,.25); }
            50%       { box-shadow: 0 0 0 12px rgba(201,162,39,.0);  }
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 700;
            margin-bottom: .65rem;
            line-height: 1.2;
        }

        .equipo {
            display: inline-block;
            background: rgba(201,162,39,.12);
            border: 1px solid rgba(201,162,39,.25);
            border-radius: .45rem;
            padding: .25rem .75rem;
            font-size: .88rem;
            font-weight: 600;
            color: var(--oro);
            margin-bottom: 1rem;
            letter-spacing: .3px;
        }

        p {
            font-size: .88rem;
            color: rgba(255,255,255,.5);
            line-height: 1.65;
            font-weight: 300;
        }

        .divider {
            width: 100%;
            height: 1px;
            background: rgba(255,255,255,.08);
            margin: 1.5rem 0;
        }

        /* Pasos de instrucción */
        .pasos {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }
        .paso {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
        }
        .paso-num {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background: rgba(201,162,39,.15);
            border: 1px solid rgba(201,162,39,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 700;
            color: var(--oro);
            flex-shrink: 0;
            margin-top: .05rem;
        }
        .paso-txt {
            font-size: .82rem;
            color: rgba(255,255,255,.48);
            line-height: 1.55;
            font-weight: 400;
        }
        .paso-txt strong {
            color: rgba(255,255,255,.75);
            font-weight: 600;
        }

        /* Botón recargar */
        .btn-reload {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.5rem;
            border: 1.5px solid rgba(255,255,255,.2);
            border-radius: 50px;
            font-size: .84rem;
            font-weight: 500;
            color: rgba(255,255,255,.7);
            background: rgba(255,255,255,.07);
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all .22s ease;
            letter-spacing: .3px;
            margin-top: 1.5rem;
            width: 100%;
            justify-content: center;
        }
        .btn-reload:hover {
            background: rgba(255,255,255,.13);
            border-color: rgba(255,255,255,.35);
            color: var(--blanco);
        }
    </style>
</head>
<body>

    <div class="fondo"></div>
    <div class="particulas" id="particulas"></div>

    <div class="pagina">

        <div class="logo">
            <div class="logo-icono"><i class="bi bi-compass-fill"></i></div>
            <div>
                <span class="logo-texto">KAIROS</span>
                <span class="logo-sub">Control Horario</span>
            </div>
        </div>

        <div class="card">
            <div class="estado-icono">
                <i class="bi bi-hourglass-split"></i>
            </div>

            <h2>Autorización pendiente</h2>

            <span class="equipo">
                <i class="bi bi-display"></i>
                {{ $computador->nombre_equipo }}
            </span>

            <p>
                Este terminal está registrado pero aún no fue habilitado por un administrador.
            </p>

            <div class="divider"></div>

            <div class="pasos">
                <div class="paso">
                    <div class="paso-num">1</div>
                    <div class="paso-txt">
                        Contacte al <strong>administrador del sistema</strong> e indíquele el nombre de este equipo.
                    </div>
                </div>
                <div class="paso">
                    <div class="paso-num">2</div>
                    <div class="paso-txt">
                        El administrador aprobará este terminal desde el panel de <strong>Dispositivos</strong>.
                    </div>
                </div>
                <div class="paso">
                    <div class="paso-num">3</div>
                    <div class="paso-txt">
                        Una vez aprobado, recargue esta página para acceder al reloj de marcas.
                    </div>
                </div>
            </div>

            <button class="btn-reload" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                Verificar autorización
            </button>
        </div>

    </div>

<script>
(function () {
    const cont = document.getElementById('particulas');
    [
        { size: 3, left: '7%',  delay: 0,   dur: 14, op: .15 },
        { size: 4, left: '22%', delay: 2.5, dur: 18, op: .10 },
        { size: 2, left: '38%', delay: 5,   dur: 12, op: .18 },
        { size: 5, left: '55%', delay: 1,   dur: 16, op: .09 },
        { size: 3, left: '72%', delay: 3.5, dur: 13, op: .14 },
        { size: 4, left: '86%', delay: 6,   dur: 17, op: .11 },
        { size: 2, left: '93%', delay: 2,   dur: 11, op: .20 },
    ].forEach(function (c) {
        const el = document.createElement('div');
        el.className = 'p';
        el.style.cssText = [
            'width:'   + c.size + 'px',
            'height:'  + c.size + 'px',
            'left:'    + c.left,
            'bottom:-8px',
            '--d:'     + c.dur   + 's',
            '--delay:' + c.delay + 's',
            '--op:'    + c.op,
        ].join(';');
        cont.appendChild(el);
    });
}());
</script>
</body>
</html>
