<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reloj de Marcas — {{ $computador->dispositivo->institucion->nombre }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }

        /* ── Variables (sistema de diseño Kairos) ───────────────── */
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
        }

        /* ── Fondo geométrico (mismo que welcome) ───────────────── */
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

        /* ── Partículas (mismo que welcome) ─────────────────────── */
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
            0%   { transform: translateY(0) scale(1);      opacity: 0; }
            15%  { opacity: var(--op, .2); }
            85%  { opacity: var(--op, .2); }
            100% { transform: translateY(-110vh) scale(.5); opacity: 0; }
        }

        /* ── Header fijo ─────────────────────────────────────────── */
        .site-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            padding: 1.4rem 2.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
            background: linear-gradient(to bottom, rgba(15,32,68,.9) 0%, transparent 100%);
            pointer-events: none;
        }

        /* Logo idéntico al welcome */
        .logo {
            display: flex;
            align-items: center;
            gap: .7rem;
        }
        .logo-icono {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--dorado), var(--oro));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--azul);
            box-shadow: 0 3px 14px rgba(201,162,39,.4);
            flex-shrink: 0;
        }
        .logo-texto {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
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

        .inst-nombre {
            font-size: .8rem;
            color: rgba(255,255,255,.42);
            text-align: right;
            max-width: 260px;
            line-height: 1.35;
            letter-spacing: .3px;
        }

        /* ── Screens ─────────────────────────────────────────────── */
        .screen {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5.5rem 2rem 2rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }
        .screen.activa {
            opacity: 1;
            pointer-events: auto;
        }

        /* ══ SCREEN 1: Reloj ════════════════════════════════════════ */
        .reloj-wrap { text-align: center; }

        .reloj-display {
            font-family: 'Inter', sans-serif;
            font-size: clamp(7rem, 23vw, 24rem);
            font-weight: 900;
            letter-spacing: .02em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            color: var(--blanco);
            text-shadow:
                0 0 80px rgba(201,162,39,.18),
                0 0 160px rgba(42,82,152,.3);
            user-select: none;
        }

        .reloj-sep { animation: blink 1s step-end infinite; }
        @keyframes blink { 50% { opacity: 0; } }

        .fecha-display {
            font-size: clamp(.85rem, 1.6vw, 1.1rem);
            color: rgba(255,255,255,.42);
            text-transform: capitalize;
            margin-top: .9rem;
            letter-spacing: .1em;
            font-weight: 400;
        }

        /* Panel DNI */
        .dni-panel {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            z-index: 11;
            padding: 1.5rem 2rem 2.75rem;
            background: linear-gradient(to top, rgba(15,32,68,1) 55%, transparent);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .6rem;
        }
        .dni-hint {
            font-size: .72rem;
            color: rgba(255,255,255,.3);
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 500;
        }
        .dni-form {
            display: flex;
            gap: .6rem;
            width: 100%;
            max-width: 370px;
        }
        .input-dni {
            flex: 1;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.14);
            border-radius: .6rem;
            color: var(--blanco);
            font-size: 1.55rem;
            font-weight: 600;
            padding: .65rem 1rem;
            outline: none;
            text-align: center;
            letter-spacing: .1em;
            font-variant-numeric: tabular-nums;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, background .2s;
        }
        .input-dni::placeholder {
            opacity: .22;
            font-size: .9rem;
            letter-spacing: 0;
            font-weight: 400;
        }
        .input-dni:focus {
            border-color: var(--dorado);
            background: rgba(201,162,39,.07);
            box-shadow: 0 0 0 3px rgba(201,162,39,.12);
        }
        .btn-id {
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 100%);
            border: none;
            border-radius: .6rem;
            color: var(--azul);
            font-size: 1.4rem;
            width: 3.2rem;
            flex-shrink: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 18px rgba(201,162,39,.38);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }
        .btn-id:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(201,162,39,.5);
            filter: brightness(1.06);
        }
        .btn-id:active { transform: scale(.95); }
        .btn-id:disabled { opacity: .38; cursor: not-allowed; }

        /* Error DNI */
        .msg-error-dni {
            min-height: 1rem;
            font-size: .8rem;
            color: rgba(255,130,100,.9);
            text-align: center;
            opacity: 0;
            transition: opacity .2s;
            letter-spacing: .3px;
        }
        .msg-error-dni.vis { opacity: 1; }

        /* ══ SCREEN 2: Verificación ══════════════════════════════════ */
        .verif-card {
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 1.25rem;
            padding: 2.25rem 2.75rem 2rem;
            width: 100%;
            max-width: 460px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            text-align: center;
        }
        .verif-sublabel {
            font-size: .68rem;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
            letter-spacing: .15em;
            font-weight: 500;
            margin-bottom: .35rem;
        }
        .verif-nombre {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.2rem, 2.8vw, 1.75rem);
            font-weight: 700;
            line-height: 1.2;
            color: var(--blanco);
        }
        .verif-hora {
            font-family: 'Inter', sans-serif;
            font-size: clamp(2.4rem, 5.5vw, 3.75rem);
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 60%, #fff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: .04em;
            margin: .9rem 0 .2rem;
        }
        .input-pass {
            background: rgba(255,255,255,.07);
            border: 1.5px solid rgba(255,255,255,.16);
            border-radius: .6rem;
            color: var(--blanco);
            font-size: 1.2rem;
            padding: .7rem 1.2rem;
            outline: none;
            width: 100%;
            text-align: center;
            letter-spacing: .2em;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, background .2s, box-shadow .2s;
            margin-top: 1rem;
        }
        .input-pass::placeholder {
            opacity: .25;
            letter-spacing: 0;
            font-size: .88rem;
        }
        .input-pass:focus {
            border-color: var(--dorado);
            background: rgba(201,162,39,.07);
            box-shadow: 0 0 0 3px rgba(201,162,39,.12);
        }
        .msg-error-verif {
            background: rgba(220,53,69,.14);
            border: 1px solid rgba(220,53,69,.32);
            border-radius: .5rem;
            color: rgba(255,140,120,.95);
            font-size: .84rem;
            padding: .5rem .9rem;
            margin-top: .85rem;
            display: none;
            text-align: center;
            letter-spacing: .2px;
        }
        .msg-error-verif.vis { display: block; }

        /* Botones verificación */
        .botones-row {
            display: flex;
            gap: .65rem;
            margin-top: 1.5rem;
        }
        /* Cancelar — mismo estilo glass que btn-acceder-nav del welcome */
        .btn-cancelar {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .85rem 1.4rem;
            border: 1.5px solid rgba(255,255,255,.2);
            border-radius: 50px;
            font-size: .88rem;
            font-weight: 500;
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.07);
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all .22s ease;
            letter-spacing: .3px;
            white-space: nowrap;
        }
        .btn-cancelar:hover {
            background: rgba(255,255,255,.13);
            border-color: rgba(255,255,255,.35);
            color: var(--blanco);
        }
        /* Confirmar — mismo estilo gold que btn-cta del welcome */
        .btn-confirmar {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .85rem 1.5rem;
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 100%);
            color: var(--azul);
            font-size: .92rem;
            font-weight: 700;
            letter-spacing: .3px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 5px 22px rgba(201,162,39,.4);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .btn-confirmar:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201,162,39,.52);
            filter: brightness(1.05);
        }
        .btn-confirmar:active { transform: translateY(0) scale(.98); }
        .btn-confirmar:disabled { opacity: .38; cursor: not-allowed; }

        /* ══ SCREEN 3: Resumen ═══════════════════════════════════════ */
        .resumen-wrap { text-align: center; }

        .resumen-icono {
            width: 5.5rem;
            height: 5.5rem;
            border-radius: 50%;
            background: rgba(201,162,39,.15);
            border: 2px solid rgba(201,162,39,.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.3rem;
            color: var(--oro);
            margin: 0 auto 1.25rem;
            animation: scaleIn .4s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes scaleIn {
            from { transform: scale(.3); opacity: 0; }
            to   { transform: scale(1);  opacity: 1; }
        }
        .resumen-titulo {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .2em;
            color: var(--oro);
            font-weight: 600;
            margin-bottom: 1.25rem;
        }
        .resumen-nombre {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.4rem, 3.2vw, 2.1rem);
            font-weight: 700;
            margin-bottom: .4rem;
        }
        .resumen-hora {
            font-family: 'Inter', sans-serif;
            font-size: clamp(2.75rem, 6.5vw, 5.5rem);
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            background: linear-gradient(135deg, var(--dorado) 0%, var(--oro) 60%, #fff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: .04em;
        }
        .resumen-fecha {
            font-size: 1rem;
            color: rgba(255,255,255,.48);
            text-transform: capitalize;
            margin-top: .4rem;
            letter-spacing: .3px;
        }
        .resumen-countdown {
            margin-top: 2.25rem;
            font-size: .75rem;
            color: rgba(255,255,255,.28);
            letter-spacing: .08em;
        }

        /* ── Spinner ─────────────────────────────────────────────── */
        .spin {
            display: inline-block;
            width: .95rem; height: .95rem;
            border: 2px solid rgba(15,32,68,.3);
            border-top-color: var(--azul);
            border-radius: 50%;
            animation: _spin .6s linear infinite;
            vertical-align: middle;
        }
        .spin-light {
            border-color: rgba(255,255,255,.25);
            border-top-color: var(--blanco);
        }
        @keyframes _spin { to { transform: rotate(360deg); } }

        .hidden { display: none !important; }
    </style>
</head>
<body>

    {{-- Fondo geométrico --}}
    <div class="fondo"></div>

    {{-- Partículas --}}
    <div class="particulas" id="particulas"></div>

    {{-- Header fijo --}}
    <header class="site-header">
        <div class="logo">
            <div class="logo-icono"><i class="bi bi-compass-fill"></i></div>
            <div>
                <span class="logo-texto">KAIROS</span>
                <span class="logo-sub">Control Horario</span>
            </div>
        </div>
        <div class="inst-nombre">{{ $computador->dispositivo->institucion->nombre }}</div>
    </header>

    {{-- ══ SCREEN 1: Reloj ════════════════════════════════════════════════════ --}}
    <div id="screen-reloj" class="screen activa">
        <div class="reloj-wrap">
            <div class="reloj-display">
                <span id="rH">00</span><span class="reloj-sep">:</span><span id="rM">00</span>
            </div>
            <div id="rFecha" class="fecha-display"></div>
        </div>

        <div class="dni-panel">
            <span class="dni-hint">Ingrese su número de documento</span>
            <div class="dni-form">
                <input id="iDni"
                       class="input-dni"
                       type="text"
                       inputmode="numeric"
                       placeholder="DNI"
                       maxlength="15"
                       autocomplete="off"
                       autofocus>
                <button id="bId" class="btn-id" onclick="identificar()">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
            <div id="eDni" class="msg-error-dni"></div>
        </div>
    </div>

    {{-- ══ SCREEN 2: Verificación ══════════════════════════════════════════════ --}}
    <div id="screen-verif" class="screen">
        <div class="verif-card">
            <div class="verif-sublabel">Registrando marca para</div>
            <div id="vNombre" class="verif-nombre"></div>

            <div id="vHora" class="verif-hora">00:00:00</div>
            <div class="verif-sublabel">Hora de marca</div>

            <input id="iPass"
                   class="input-pass hidden"
                   type="password"
                   inputmode="numeric"
                   placeholder="PIN de 4 dígitos"
                   maxlength="4"
                   autocomplete="off">

            <div id="eVerif" class="msg-error-verif"></div>

            <div class="botones-row">
                <button class="btn-cancelar" onclick="cancelar()">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button id="bConf" class="btn-confirmar" onclick="confirmar()">
                    <i class="bi bi-check-lg"></i> Confirmar marca
                </button>
            </div>
        </div>
    </div>

    {{-- ══ SCREEN 3: Resumen ═══════════════════════════════════════════════════ --}}
    <div id="screen-resumen" class="screen">
        <div class="resumen-wrap">
            <div class="resumen-icono"><i class="bi bi-check-lg"></i></div>
            <div class="resumen-titulo">Marca registrada</div>
            <div id="sNombre" class="resumen-nombre"></div>
            <div id="sHora"   class="resumen-hora"></div>
            <div id="sFecha"  class="resumen-fecha"></div>
            <div class="resumen-countdown">
                Volviendo en <span id="sCnt">5</span> segundos
            </div>
        </div>
    </div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Offset servidor — calculado una vez al cargar la página
const SRV_OFFSET = {{ now()->timestamp * 1000 }} - Date.now();
function ahora() { return new Date(Date.now() + SRV_OFFSET); }
function pad(n)  { return String(n).padStart(2, '0'); }

const DIAS  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
const MESES = ['enero','febrero','marzo','abril','mayo','junio','julio',
               'agosto','septiembre','octubre','noviembre','diciembre'];

// ── Reloj principal ───────────────────────────────────────────────────────────
function tickReloj() {
    const t = ahora();
    document.getElementById('rH').textContent = pad(t.getHours());
    document.getElementById('rM').textContent = pad(t.getMinutes());
    document.getElementById('rFecha').textContent =
        DIAS[t.getDay()] + ' ' + t.getDate() + ' de ' +
        MESES[t.getMonth()] + ' de ' + t.getFullYear();
}
tickReloj();
setInterval(tickReloj, 1000);

// ── Reloj de verificación ─────────────────────────────────────────────────────
let timerVerif = null;
function tickVerif() {
    const t = ahora();
    document.getElementById('vHora').textContent =
        pad(t.getHours()) + ':' + pad(t.getMinutes()) + ':' + pad(t.getSeconds());
}
function iniciarRelojVerif() { tickVerif(); timerVerif = setInterval(tickVerif, 1000); }
function detenerRelojVerif() { clearInterval(timerVerif); timerVerif = null; }

// ── Estado ────────────────────────────────────────────────────────────────────
let docActual    = '';
let requierePass = false;
let timerCnt     = null;

// ── Navegación ────────────────────────────────────────────────────────────────
function irA(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('activa'));
    document.getElementById(id).classList.add('activa');
}

// ── Identificar usuario por DNI ───────────────────────────────────────────────
async function identificar() {
    const doc = document.getElementById('iDni').value.trim();
    if (!doc) { setError('eDni', 'Ingrese su número de documento.'); return; }

    const btn = document.getElementById('bId');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span>';
    clearError('eDni');

    try {
        const r = await apiPost('{{ route("marca-web.identificar") }}', {
            documento: doc,
        });

        if (!r.ok || !r.json.ok) {
            setError('eDni', r.json.error ?? 'No se pudo identificar al usuario.');
            return;
        }

        docActual    = doc;
        requierePass = r.json.requiere_contrasena;

        document.getElementById('vNombre').textContent = r.json.nombre;
        document.getElementById('iPass').value = '';
        document.getElementById('iPass').classList.toggle('hidden', !requierePass);
        clearError('eVerif');

        irA('screen-verif');
        iniciarRelojVerif();

        setTimeout(() => (requierePass
            ? document.getElementById('iPass')
            : document.getElementById('bConf')
        ).focus(), 150);

    } catch {
        setError('eDni', 'Error de conexión. Intente nuevamente.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-right"></i>';
    }
}

// ── Cancelar ──────────────────────────────────────────────────────────────────
function cancelar() {
    detenerRelojVerif();
    docActual = '';
    document.getElementById('iDni').value  = '';
    document.getElementById('iPass').value = '';
    clearError('eDni');
    clearError('eVerif');
    irA('screen-reloj');
    setTimeout(() => document.getElementById('iDni').focus(), 150);
}

// ── Confirmar y registrar marca ───────────────────────────────────────────────
async function confirmar() {
    const btn  = document.getElementById('bConf');
    const pass = document.getElementById('iPass').value;

    if (requierePass && !pass) {
        setError('eVerif', 'Ingrese su contraseña.');
        document.getElementById('iPass').focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> Registrando...';
    clearError('eVerif');

    const payload = { documento: docActual };
    if (requierePass) payload.password = pass;

    try {
        const r = await apiPost('{{ route("marca-web.confirmar") }}', payload);

        if (!r.ok || !r.json.ok) {
            setError('eVerif', r.json.error ?? 'Error al registrar la marca.');
            return;
        }

        detenerRelojVerif();
        mostrarResumen(r.json.nombre, r.json.hora, r.json.fecha);

    } catch {
        setError('eVerif', 'Error de conexión. Intente nuevamente.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar marca';
    }
}

// ── Pantalla de resumen (5 segundos) ──────────────────────────────────────────
function mostrarResumen(nombre, hora, fecha) {
    document.getElementById('sNombre').textContent = nombre;
    document.getElementById('sHora').textContent   = hora;
    document.getElementById('sFecha').textContent  = fecha;
    irA('screen-resumen');

    let seg = 5;
    document.getElementById('sCnt').textContent = seg;
    timerCnt = setInterval(() => {
        seg--;
        document.getElementById('sCnt').textContent = seg;
        if (seg <= 0) { clearInterval(timerCnt); timerCnt = null; volverAlReloj(); }
    }, 1000);
}

function volverAlReloj() {
    docActual = '';
    document.getElementById('iDni').value  = '';
    document.getElementById('iPass').value = '';
    irA('screen-reloj');
    setTimeout(() => document.getElementById('iDni').focus(), 150);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
async function apiPost(url, data) {
    const resp = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    });
    return { ok: resp.ok, status: resp.status, json: await resp.json() };
}

function setError(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.classList.add('vis');
}
function clearError(id) {
    const el = document.getElementById(id);
    el.textContent = '';
    el.classList.remove('vis');
}

// ── Teclado ───────────────────────────────────────────────────────────────────
document.getElementById('iDni').addEventListener('keydown',  e => { if (e.key === 'Enter') identificar(); });
document.getElementById('iPass').addEventListener('keydown', e => { if (e.key === 'Enter') confirmar(); });

// ── Partículas flotantes (mismo que welcome) ──────────────────────────────────
(function () {
    const cont = document.getElementById('particulas');
    [
        { size: 3, left: '7%',  delay: 0,   dur: 14, op: .15 },
        { size: 4, left: '19%', delay: 2.5, dur: 18, op: .10 },
        { size: 2, left: '33%', delay: 5,   dur: 12, op: .18 },
        { size: 5, left: '50%', delay: 1,   dur: 16, op: .09 },
        { size: 3, left: '67%', delay: 3.5, dur: 13, op: .14 },
        { size: 4, left: '80%', delay: 6,   dur: 17, op: .11 },
        { size: 2, left: '91%', delay: 2,   dur: 11, op: .20 },
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
