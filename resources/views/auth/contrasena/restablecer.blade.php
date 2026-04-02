<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairos — Restablecer Contraseña</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
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
            background: var(--gris-fondo);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        .contenedor {
            width: 100%;
            max-width: 440px;
            padding: 1.5rem;
            animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) both;
        }

        .cabecera {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icono {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--dorado), var(--dorado-claro));
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--azul-marino);
            box-shadow: 0 4px 20px rgba(201,162,39,.35);
            margin-bottom: 1rem;
        }

        .logo-nombre {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--azul-marino);
            letter-spacing: 4px;
            display: block;
        }

        .tarjeta {
            background: var(--blanco);
            border-radius: 18px;
            padding: 2.25rem 2.5rem 2rem;
            box-shadow: 0 4px 32px rgba(15,32,68,.09), 0 1px 4px rgba(15,32,68,.06);
        }

        .titulo-tarjeta {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--azul-marino);
            margin-bottom: .3rem;
        }

        .subtitulo-tarjeta {
            font-size: .84rem;
            color: var(--texto-suave);
            margin-bottom: 1.75rem;
        }

        .alerta-error {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            background: #fff5f5;
            border: 1.5px solid #feb2b2;
            border-radius: 10px;
            padding: .85rem 1rem;
            font-size: .83rem;
            color: #c53030;
            margin-bottom: 1.5rem;
        }

        .alerta-error i { flex-shrink: 0; margin-top: 1px; }

        /* Indicador de fortaleza */
        .fortaleza-barra {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            background: var(--gris-borde);
            overflow: hidden;
        }

        .fortaleza-relleno {
            height: 100%;
            border-radius: 2px;
            width: 0%;
            transition: width .3s, background .3s;
        }

        .fortaleza-texto {
            font-size: .72rem;
            margin-top: 4px;
            color: var(--texto-suave);
        }

        /* Campos */
        .campo-icono {
            position: relative;
            margin-bottom: 1.2rem;
        }

        .campo-icono label {
            display: block;
            font-size: .8rem;
            font-weight: 500;
            color: var(--texto);
            margin-bottom: .4rem;
        }

        .campo-icono .icono-prefijo {
            position: absolute;
            left: 14px;
            bottom: 13px;
            color: #b0bec5;
            font-size: .95rem;
            pointer-events: none;
            transition: color .2s;
        }

        .campo-icono:focus-within .icono-prefijo { color: var(--azul-medio); }

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
            outline: none;
        }

        .campo-icono .form-control.is-invalid {
            border-color: #e53e3e;
            background: #fff5f5;
        }

        .btn-ojo {
            position: absolute;
            right: 12px;
            bottom: 10px;
            background: none;
            border: none;
            color: #b0bec5;
            cursor: pointer;
            font-size: .95rem;
            padding: 3px 5px;
            transition: color .2s;
        }

        .btn-ojo:hover { color: var(--azul-medio); }

        .invalid-feedback { font-size: .76rem; margin-top: 4px; }

        .btn-restablecer {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--azul-marino) 0%, var(--azul-claro) 100%);
            border: none;
            border-radius: 11px;
            color: #fff;
            font-size: .92rem;
            font-weight: 600;
            letter-spacing: .4px;
            margin-top: .25rem;
            transition: opacity .2s, transform .2s, box-shadow .2s;
            box-shadow: 0 4px 18px rgba(15,32,68,.28);
            cursor: pointer;
        }

        .btn-restablecer:hover:not(:disabled) {
            opacity: .93;
            transform: translateY(-1px);
            box-shadow: 0 7px 22px rgba(15,32,68,.38);
        }

        .btn-restablecer:disabled { opacity: .68; cursor: not-allowed; }

        .link-volver {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            margin-top: 1.4rem;
            font-size: .82rem;
            color: var(--texto-suave);
            text-decoration: none;
            transition: color .2s;
        }

        .link-volver:hover { color: var(--azul-medio); }
    </style>
</head>
<body>

<div class="contenedor">

    <div class="cabecera">
        <div class="logo-icono">
            <i class="bi bi-compass-fill"></i>
        </div>
        <span class="logo-nombre">KAIROS</span>
    </div>

    <div class="tarjeta">

        <h1 class="titulo-tarjeta">Nueva contraseña</h1>
        <p class="subtitulo-tarjeta">Elegí una contraseña segura de al menos 8 caracteres.</p>

        @if ($errors->any())
            <div class="alerta-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" id="formReset" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            {{-- Nueva contraseña --}}
            <div class="campo-icono">
                <label for="password">Nueva contraseña</label>
                <i class="bi bi-lock icono-prefijo"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Mínimo 8 caracteres"
                    autocomplete="new-password"
                    autofocus
                >
                <button type="button" class="btn-ojo" data-target="password" aria-label="Mostrar contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="fortaleza-barra"><div class="fortaleza-relleno" id="barrFortaleza"></div></div>
                <div class="fortaleza-texto" id="textoFortaleza"></div>
            </div>

            {{-- Confirmar contraseña --}}
            <div class="campo-icono">
                <label for="password_confirmation">Confirmar contraseña</label>
                <i class="bi bi-lock-fill icono-prefijo"></i>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Repetí la contraseña"
                    autocomplete="new-password"
                >
                <button type="button" class="btn-ojo" data-target="password_confirmation" aria-label="Mostrar contraseña" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-restablecer" id="btnRestablecer">
                <span id="textoBtn">
                    <i class="bi bi-check2-circle me-1"></i>Restablecer contraseña
                </span>
                <span id="spinnerBtn" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Guardando...
                </span>
            </button>

        </form>

        <a href="{{ route('login') }}" class="link-volver">
            <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
        </a>

    </div>

</div>

<script>
(function () {

    // Toggle visibilidad de contraseñas
    document.querySelectorAll('.btn-ojo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input  = document.getElementById(this.dataset.target);
            const icono  = this.querySelector('i');
            const visible = input.type === 'text';
            input.type     = visible ? 'password' : 'text';
            icono.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    });

    // Indicador de fortaleza
    const inputPass     = document.getElementById('password');
    const barraRelleno  = document.getElementById('barrFortaleza');
    const textoFortaleza = document.getElementById('textoFortaleza');

    const niveles = [
        { min: 0,  color: '#e53e3e', label: 'Muy débil',  pct: 15  },
        { min: 1,  color: '#dd6b20', label: 'Débil',       pct: 35  },
        { min: 2,  color: '#d69e2e', label: 'Regular',     pct: 60  },
        { min: 3,  color: '#38a169', label: 'Fuerte',      pct: 80  },
        { min: 4,  color: '#2b6cb0', label: 'Muy fuerte',  pct: 100 },
    ];

    function calcularFortaleza(pwd) {
        if (!pwd) return -1;
        let puntos = 0;
        if (pwd.length >= 8)  puntos++;
        if (pwd.length >= 12) puntos++;
        if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) puntos++;
        if (/\d/.test(pwd))   puntos++;
        if (/[^A-Za-z0-9]/.test(pwd)) puntos++;
        return Math.min(puntos, 4);
    }

    inputPass.addEventListener('input', function () {
        const nivel = calcularFortaleza(this.value);
        if (nivel < 0) {
            barraRelleno.style.width = '0%';
            textoFortaleza.textContent = '';
            return;
        }
        const n = niveles[nivel];
        barraRelleno.style.width      = n.pct + '%';
        barraRelleno.style.background = n.color;
        textoFortaleza.textContent    = n.label;
        textoFortaleza.style.color    = n.color;
    });

    // Spinner al enviar
    document.getElementById('formReset').addEventListener('submit', function () {
        document.getElementById('textoBtn').classList.add('d-none');
        document.getElementById('spinnerBtn').classList.remove('d-none');
        document.getElementById('btnRestablecer').disabled = true;
    });

}());
</script>

</body>
</html>
