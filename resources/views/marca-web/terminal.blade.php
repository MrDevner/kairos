<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal de Marcas — {{ $computador->dispositivo->institucion->nombre }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1B4F72 0%, #0d2b40 60%, #1a3a4f 100%);
            color: #fff;
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ── Encabezado ─────────────────────────────────────────────── */
        .terminal-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .terminal-header .logo {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: .1em;
            color: #75AADB;
        }
        .terminal-header .logo i { margin-right: .4rem; }
        .terminal-header .inst-name {
            font-size: .95rem;
            opacity: .75;
            margin-top: .25rem;
        }

        /* ── Reloj ──────────────────────────────────────────────────── */
        .reloj {
            font-size: 5rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-align: center;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }
        .fecha-hoy {
            text-align: center;
            font-size: 1.1rem;
            opacity: .75;
            text-transform: capitalize;
            margin-top: .5rem;
            margin-bottom: 2.5rem;
        }

        /* ── Panel de marca ─────────────────────────────────────────── */
        .panel {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 1rem;
            padding: 2rem 2.5rem;
            width: 100%;
            max-width: 480px;
            backdrop-filter: blur(6px);
        }

        .campo-documento {
            display: flex;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .campo-documento input {
            flex: 1;
            background: rgba(255,255,255,.1);
            border: 1.5px solid rgba(255,255,255,.25);
            border-radius: .5rem;
            color: #fff;
            font-size: 1.3rem;
            padding: .6rem 1rem;
            outline: none;
            transition: border-color .2s;
        }
        .campo-documento input::placeholder { opacity: .5; }
        .campo-documento input:focus { border-color: #75AADB; }

        .btns-marca {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .btn-marca {
            border: none;
            border-radius: .75rem;
            font-size: 1.2rem;
            font-weight: 700;
            padding: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: transform .1s, opacity .2s;
        }
        .btn-marca:active { transform: scale(.97); }
        .btn-entrada { background: #198754; color: #fff; }
        .btn-salida  { background: #dc3545; color: #fff; }
        .btn-marca:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Feedback ───────────────────────────────────────────────── */
        #feedback {
            margin-top: 1.25rem;
            border-radius: .5rem;
            padding: .75rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            display: none;
        }
        #feedback.ok    { background: rgba(25,135,84,.3); border: 1px solid #198754; }
        #feedback.error { background: rgba(220,53,69,.3);  border: 1px solid #dc3545; }

        /* ── Historial ──────────────────────────────────────────────── */
        .historial {
            margin-top: 2rem;
            width: 100%;
            max-width: 480px;
        }
        .historial h6 {
            font-size: .8rem;
            opacity: .6;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .6rem;
        }
        .historial-item {
            background: rgba(255,255,255,.05);
            border-radius: .5rem;
            padding: .5rem .9rem;
            margin-bottom: .4rem;
            display: flex;
            justify-content: space-between;
            font-size: .9rem;
        }
        .historial-item .hora { opacity: .65; font-variant-numeric: tabular-nums; }

        /* ── Spinner ────────────────────────────────────────────────── */
        .spinner {
            display: inline-block;
            width: 1rem; height: 1rem;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="terminal-header">
        <div class="logo"><i class="bi bi-compass-fill"></i>KAIROS</div>
        <div class="inst-name">{{ $computador->dispositivo->institucion->nombre }}</div>
    </div>

    {{-- Reloj --}}
    <div class="reloj" id="reloj">00:00:00</div>
    <div class="fecha-hoy" id="fechaHoy"></div>

    {{-- Panel de marca --}}
    <div class="panel">
        <div class="campo-documento">
            <input type="text"
                   id="documento"
                   placeholder="N° de documento"
                   inputmode="numeric"
                   maxlength="15"
                   autofocus
                   autocomplete="off">
        </div>

        <div class="btns-marca">
            <button class="btn-marca btn-entrada" id="btnEntrada" onclick="marcar()">
                <i class="bi bi-box-arrow-in-right"></i> ENTRADA
            </button>
            <button class="btn-marca btn-salida" id="btnSalida" onclick="marcar()">
                <i class="bi bi-box-arrow-right"></i> SALIDA
            </button>
        </div>

        <div id="feedback"></div>
    </div>

    {{-- Historial --}}
    <div class="historial">
        <h6><i class="bi bi-clock-history me-1"></i>Últimas marcas registradas</h6>
        <div id="historialLista">
            @forelse($ultimas as $u)
                <div class="historial-item">
                    <span>{{ $u->usuario?->nombre_completo ?? $u->id_usuario }}</span>
                    <span class="hora">{{ $u->fecha_hora->format('H:i:s') }}</span>
                </div>
            @empty
                <div class="historial-item"><span>Sin marcas recientes</span></div>
            @endforelse
        </div>
    </div>

<script>
const FINGERPRINT = '{{ $computador->fingerprint }}';
const CSRF        = document.querySelector('meta[name="csrf-token"]').content;

// ── Reloj en tiempo real ─────────────────────────────────────────────────
function actualizarReloj() {
    const ahora = new Date();
    const h = String(ahora.getHours()).padStart(2,'0');
    const m = String(ahora.getMinutes()).padStart(2,'0');
    const s = String(ahora.getSeconds()).padStart(2,'0');
    document.getElementById('reloj').textContent = `${h}:${m}:${s}`;

    const dias   = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    const meses  = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    document.getElementById('fechaHoy').textContent =
        `${dias[ahora.getDay()]} ${ahora.getDate()} de ${meses[ahora.getMonth()]} de ${ahora.getFullYear()}`;
}
actualizarReloj();
setInterval(actualizarReloj, 1000);

// ── Marca ────────────────────────────────────────────────────────────────
async function marcar() {
    const documento = document.getElementById('documento').value.trim();
    if (!documento) {
        mostrarFeedback('Ingrese su número de documento.', false);
        document.getElementById('documento').focus();
        return;
    }

    setBloqueado(true);

    try {
        const resp = await fetch('{{ route("marca-web.marcar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ documento, fingerprint: FINGERPRINT }),
        });

        const data = await resp.json();

        if (resp.ok && data.ok) {
            mostrarFeedback(`✓ ${data.nombre} — ${data.hora}`, true);
            agregarHistorial(data.nombre, data.hora);
            document.getElementById('documento').value = '';
        } else {
            mostrarFeedback(data.error ?? 'Error al registrar la marca.', false);
        }
    } catch (e) {
        mostrarFeedback('Error de conexión. Intente nuevamente.', false);
    } finally {
        setBloqueado(false);
        document.getElementById('documento').focus();
    }
}

// Enter en campo documento
document.getElementById('documento').addEventListener('keydown', e => {
    if (e.key === 'Enter') marcar();
});

function mostrarFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.className   = ok ? 'ok' : 'error';
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, ok ? 4000 : 5000);
}

function setBloqueado(bloqueado) {
    document.getElementById('btnEntrada').disabled = bloqueado;
    document.getElementById('btnSalida').disabled  = bloqueado;
}

function agregarHistorial(nombre, hora) {
    const lista = document.getElementById('historialLista');
    const div = document.createElement('div');
    div.className = 'historial-item';
    div.innerHTML = `<span>${nombre}</span><span class="hora">${hora}</span>`;
    lista.insertBefore(div, lista.firstChild);
    // Mantener máximo 5
    while (lista.children.length > 5) lista.removeChild(lista.lastChild);
}
</script>
</body>
</html>
