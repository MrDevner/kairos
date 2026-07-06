<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del servidor — Kairos</title>
    <style>
        :root {
            --azul: #1B4F72;
            --celeste: #75AADB;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f6f8;
            color: #212529;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        .card {
            background: #fff;
            border-radius: .5rem;
            box-shadow: 0 .25rem 1rem rgba(0,0,0,.08);
            max-width: 640px;
            width: 100%;
            overflow: hidden;
        }
        .card-header {
            background: var(--azul);
            color: #fff;
            padding: 1.25rem 1.5rem;
        }
        .card-header h1 { font-size: 1.15rem; margin: 0 0 .25rem; }
        .card-header p { margin: 0; opacity: .85; font-size: .85rem; }
        .card-body { padding: 1.5rem; }
        .correlation-box {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: #f1f3f5;
            border-radius: .375rem;
            padding: .6rem .8rem;
            font-family: "SFMono-Regular", Consolas, monospace;
            font-size: .85rem;
            margin-bottom: 1rem;
        }
        .correlation-box code { flex: 1; word-break: break-all; }
        .btn {
            border: none;
            border-radius: .375rem;
            padding: .4rem .75rem;
            font-size: .8rem;
            cursor: pointer;
            background: var(--azul);
            color: #fff;
        }
        .btn:hover { opacity: .9; }
        .btn-outline {
            background: transparent;
            color: var(--azul);
            border: 1px solid var(--azul);
        }
        dl.meta { font-size: .82rem; margin: 0 0 1rem; }
        dl.meta dt { color: #6c757d; float: left; width: 90px; clear: left; }
        dl.meta dd { margin: 0 0 .35rem 90px; word-break: break-all; }
        details { margin-bottom: 1rem; font-size: .85rem; }
        details summary { cursor: pointer; color: var(--azul); font-weight: 600; }
        details ul { margin: .5rem 0 0; padding-left: 1.2rem; color: #495057; }
        .debug { background: #212529; color: #f8f9fa; border-radius: .375rem; padding: 1rem; font-size: .75rem; overflow-x: auto; }
        .debug pre { margin: .5rem 0 0; white-space: pre-wrap; word-break: break-word; }
        .contacto { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1>Ocurrió un error inesperado</h1>
            <p>El equipo técnico ya fue notificado. Podés usar el código de abajo para reportarlo.</p>
        </div>
        <div class="card-body">
            @if($correlationId)
            <div class="correlation-box">
                <code id="correlation-id">{{ $correlationId }}</code>
                <button type="button" class="btn btn-outline" onclick="copiarCorrelationId()">Copiar</button>
            </div>
            @endif

            <dl class="meta">
                <dt>Fecha</dt>
                <dd>{{ now()->format('d/m/Y H:i:s') }}</dd>
                <dt>URL</dt>
                <dd>{{ request()->fullUrl() }}</dd>
                <dt>Navegador</dt>
                <dd>{{ request()->userAgent() }}</dd>
            </dl>

            <details>
                <summary>¿Qué datos incluir en el reporte?</summary>
                <ul>
                    <li>El código de correlación de arriba.</li>
                    <li>Qué estabas intentando hacer cuando ocurrió el error.</li>
                    <li>Si el error se repite siempre o solo a veces.</li>
                    <li>Una captura de pantalla, si es posible.</li>
                </ul>
            </details>

            @if($exception)
            <div class="debug mb-3">
                <strong>{{ get_class($exception) }}</strong>: {{ $exception->getMessage() }}
                <div>{{ $exception->getFile() }}:{{ $exception->getLine() }}</div>
                <pre>{{ $exception->getTraceAsString() }}</pre>
            </div>
            @endif

            <div class="contacto">
                @if(auth()->check())
                    <a class="btn" href="{{ route('tickets.create') }}">Abrir un ticket de soporte</a>
                @endif
                <a class="btn btn-outline" href="{{ route('home') }}">Volver al inicio</a>
            </div>
        </div>
    </div>

    <script>
        function copiarCorrelationId() {
            const texto = document.getElementById('correlation-id').innerText;
            navigator.clipboard?.writeText(texto);
        }
    </script>
</body>
</html>
