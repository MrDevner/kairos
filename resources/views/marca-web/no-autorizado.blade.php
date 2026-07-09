<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrar terminal — KAIROS</title>

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
            max-width: 440px;
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
            text-decoration: none;
            color: inherit;
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
            padding: 2.5rem 2.75rem 2.25rem;
            width: 100%;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            text-align: center;
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(201,162,39,.13);
            border: 1px solid rgba(201,162,39,.28);
            border-radius: 50px;
            padding: .35rem .9rem;
            font-size: .68rem;
            font-weight: 500;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: var(--oro);
            margin-bottom: 1.5rem;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: .6rem;
            line-height: 1.2;
        }
        .subtitulo {
            font-size: .88rem;
            color: rgba(255,255,255,.5);
            line-height: 1.6;
            margin-bottom: 1.75rem;
            font-weight: 300;
        }

        /* Inputs */
        .campo {
            margin-bottom: 1rem;
            text-align: left;
        }
        .campo label {
            display: block;
            font-size: .72rem;
            font-weight: 500;
            color: rgba(255,255,255,.45);
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: .4rem;
        }
        .campo input {
            width: 100%;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.14);
            border-radius: .6rem;
            color: var(--blanco);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            padding: .7rem 1rem;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .campo input::placeholder { color: rgba(255,255,255,.22); }
        .campo input:focus {
            border-color: var(--dorado);
            background: rgba(201,162,39,.07);
            box-shadow: 0 0 0 3px rgba(201,162,39,.12);
        }

        /* Botón primario */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 100%);
            color: var(--azul);
            font-size: .95rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            letter-spacing: .3px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 5px 22px rgba(201,162,39,.4);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
            margin-top: .5rem;
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201,162,39,.52);
            filter: brightness(1.05);
        }
        .btn-primary:active { transform: translateY(0) scale(.98); }
        .btn-primary:disabled { opacity: .4; cursor: not-allowed; }

        /* Mensaje resultado */
        .msg {
            margin-top: 1.1rem;
            padding: .65rem 1rem;
            border-radius: .55rem;
            font-size: .86rem;
            text-align: center;
            display: none;
            letter-spacing: .2px;
        }
        .msg.ok {
            display: block;
            background: rgba(201,162,39,.14);
            border: 1px solid rgba(201,162,39,.3);
            color: var(--oro);
        }
        .msg.error {
            display: block;
            background: rgba(220,53,69,.14);
            border: 1px solid rgba(220,53,69,.3);
            color: rgba(255,140,120,.95);
        }

        /* Divisor */
        .divider {
            width: 100%;
            height: 1px;
            background: rgba(255,255,255,.08);
            margin: 1.5rem 0;
        }

        /* Info fingerprint */
        .fp-info {
            font-size: .76rem;
            color: rgba(255,255,255,.28);
            line-height: 1.5;
            letter-spacing: .2px;
        }

        /* Spinner */
        .spin {
            display: inline-block;
            width: .9rem; height: .9rem;
            border: 2px solid rgba(15,32,68,.3);
            border-top-color: var(--azul);
            border-radius: 50%;
            animation: _spin .6s linear infinite;
            vertical-align: middle;
        }
        @keyframes _spin { to { transform: rotate(360deg); } }
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
            <div class="badge">
                <i class="bi bi-hdd-network"></i>
                Nuevo terminal
            </div>

            <h2>Terminal no registrado</h2>
            <p class="subtitulo">
                Este equipo no está autorizado para registrar marcas.<br>
                Complete el formulario para solicitar el acceso.
            </p>

            <div class="campo">
                <label>Nombre del equipo</label>
                <input id="nombre" type="text" placeholder="Ej: PC-Recepción-01" required>
            </div>

            <div class="campo">
                <label>ID del dispositivo asignado</label>
                <input id="dispositivo" type="number" min="1" placeholder="Número de dispositivo" required>
            </div>

            <button class="btn-primary" id="btnSolicitar" onclick="solicitar()">
                <i class="bi bi-send"></i>
                Solicitar autorización
            </button>

            <div id="msg" class="msg"></div>

            <div class="divider"></div>

            <div class="fp-info">
                Al solicitar la autorización, este equipo queda identificado
                mediante una marca que el servidor deja en esta PC.
            </div>
        </div>

    </div>

<script>
async function solicitar() {
    const nombre      = document.getElementById('nombre').value.trim();
    const dispositivo = parseInt(document.getElementById('dispositivo').value);
    const btn         = document.getElementById('btnSolicitar');
    const msgEl       = document.getElementById('msg');

    if (!nombre || !dispositivo) {
        msgEl.className = 'msg error';
        msgEl.textContent = 'Complete todos los campos antes de continuar.';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> Enviando…';
    msgEl.className = 'msg';

    try {
        const resp = await fetch('{{ route("marca-web.solicitar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nombre_equipo: nombre, id_dispositivo: dispositivo }),
        });

        const data = await resp.json();

        if (resp.ok && data.ok) {
            msgEl.className = 'msg ok';
            msgEl.textContent = data.mensaje ?? 'Solicitud enviada. Espere la aprobación del administrador.';
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Solicitud enviada';
            setTimeout(() => window.location.reload(), 1200);
        } else {
            msgEl.className = 'msg error';
            msgEl.textContent = data.message ?? data.error ?? 'Error al enviar la solicitud.';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i> Solicitar autorización';
        }
    } catch {
        msgEl.className = 'msg error';
        msgEl.textContent = 'Error de conexión. Intente nuevamente.';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send"></i> Solicitar autorización';
    }
}

// Partículas flotantes
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
