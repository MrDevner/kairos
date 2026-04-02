<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairos — Recuperar Contraseña</title>

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

        /* Encabezado con logo */
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

        /* Tarjeta */
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
            line-height: 1.5;
        }

        /* Alerta de éxito */
        .alerta-exito {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            background: #f0fff4;
            border: 1.5px solid #9ae6b4;
            border-radius: 10px;
            padding: .85rem 1rem;
            font-size: .84rem;
            color: #276749;
            margin-bottom: 1.5rem;
        }

        .alerta-exito i { flex-shrink: 0; margin-top: 1px; }

        /* Alerta de error */
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

        /* Campo con ícono */
        .campo-icono {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .campo-icono label {
            display: block;
            font-size: .8rem;
            font-weight: 500;
            color: var(--texto);
            margin-bottom: .4rem;
            letter-spacing: .2px;
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

        .invalid-feedback { font-size: .76rem; margin-top: 4px; }

        /* Botón enviar */
        .btn-enviar {
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

        .btn-enviar:hover:not(:disabled) {
            opacity: .93;
            transform: translateY(-1px);
            box-shadow: 0 7px 22px rgba(15,32,68,.38);
        }

        .btn-enviar:disabled { opacity: .68; cursor: not-allowed; }

        /* Link volver */
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

        <h1 class="titulo-tarjeta">Recuperar contraseña</h1>
        <p class="subtitulo-tarjeta">
            Ingresá tu número de documento y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        @if (session('status'))
            <div class="alerta-exito">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alerta-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="formRecuperar" novalidate>
            @csrf

            <div class="campo-icono">
                <label for="documento">Número de documento</label>
                <i class="bi bi-person-vcard icono-prefijo"></i>
                <input
                    type="text"
                    id="documento"
                    name="documento"
                    class="form-control @error('documento') is-invalid @enderror"
                    placeholder="Ej: 28479741"
                    value="{{ old('documento') }}"
                    autocomplete="off"
                    inputmode="numeric"
                    autofocus
                >
                @error('documento')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-enviar" id="btnEnviar">
                <span id="textoBtn">
                    <i class="bi bi-send me-1"></i>Enviar enlace
                </span>
                <span id="spinnerBtn" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Enviando...
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
    // Solo dígitos en el campo documento
    const inputDoc = document.getElementById('documento');
    inputDoc.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    // Spinner al enviar
    document.getElementById('formRecuperar').addEventListener('submit', function () {
        document.getElementById('textoBtn').classList.add('d-none');
        document.getElementById('spinnerBtn').classList.remove('d-none');
        document.getElementById('btnEnviar').disabled = true;
    });
}());
</script>

</body>
</html>
