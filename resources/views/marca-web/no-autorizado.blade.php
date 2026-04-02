<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal no autorizado — KAIROS</title>
    <style>
        body { min-height:100vh; background:linear-gradient(135deg,#1B4F72,#0d2b40); color:#fff; font-family:system-ui,sans-serif; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .box { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); border-radius:1rem; padding:2.5rem; max-width:460px; width:100%; text-align:center; }
        .logo { font-size:1.6rem; font-weight:800; color:#75AADB; margin-bottom:1rem; }
        h2 { font-size:1.2rem; margin-bottom:.5rem; }
        p  { opacity:.7; font-size:.9rem; margin-bottom:1.5rem; }
        input { width:100%; background:rgba(255,255,255,.1); border:1.5px solid rgba(255,255,255,.25); border-radius:.5rem; color:#fff; font-size:1rem; padding:.6rem 1rem; margin-bottom:.75rem; outline:none; }
        input::placeholder { opacity:.5; }
        button { width:100%; background:#1B4F72; border:none; border-radius:.5rem; color:#fff; font-size:1rem; font-weight:700; padding:.75rem; cursor:pointer; }
        button:hover { background:#2471A3; }
        #msg { margin-top:1rem; font-size:.9rem; opacity:.8; }
    </style>
</head>
<body>
<div class="box">
    <div class="logo">⌚ KAIROS</div>
    <h2>Terminal no registrado</h2>
    <p>Este equipo no está registrado en el sistema. Complete el formulario para solicitar autorización.</p>

    <input type="text" id="nombre" placeholder="Nombre del equipo (ej: PC-Lab-01)" required>
    <input type="number" id="dispositivo" placeholder="ID del dispositivo" required>
    <button onclick="solicitar()">Solicitar autorización</button>
    <div id="msg"></div>
</div>

<script>
// Generar fingerprint simple basado en userAgent + pantalla
const fp = btoa([navigator.userAgent, screen.width, screen.height, navigator.language].join('|')).slice(0,40);

// Redirigir si ya hay fp en la URL
const url = new URL(window.location.href);
if (!url.searchParams.get('fp')) {
    url.searchParams.set('fp', fp);
    window.history.replaceState({}, '', url);
}

async function solicitar() {
    const nombre     = document.getElementById('nombre').value.trim();
    const dispositivo = parseInt(document.getElementById('dispositivo').value);
    if (!nombre || !dispositivo) { document.getElementById('msg').textContent = 'Complete todos los campos.'; return; }

    const resp = await fetch('{{ route("marca-web.solicitar") }}', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
        body: JSON.stringify({ fingerprint: fp, nombre_equipo: nombre, id_dispositivo: dispositivo }),
    });
    const data = await resp.json();
    document.getElementById('msg').textContent = data.mensaje ?? data.error ?? 'Error.';
}
</script>
</body>
</html>
