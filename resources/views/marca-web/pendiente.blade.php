<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorización pendiente — KAIROS</title>
    <style>
        body { min-height:100vh; background:linear-gradient(135deg,#1B4F72,#0d2b40); color:#fff; font-family:system-ui,sans-serif; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .box { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); border-radius:1rem; padding:2.5rem; max-width:420px; text-align:center; }
        .icon { font-size:3.5rem; margin-bottom:1rem; }
        h2 { margin-bottom:.5rem; }
        p  { opacity:.7; font-size:.9rem; }
    </style>
</head>
<body>
<div class="box">
    <div class="icon">⏳</div>
    <h2>Autorización pendiente</h2>
    <p>El equipo <strong>{{ $computador->nombre_equipo }}</strong> está registrado pero aún no fue autorizado.</p>
    <p style="margin-top:1rem">Contacte al administrador del sistema para habilitar este terminal.</p>
</div>
</body>
</html>
