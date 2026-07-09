<?php

namespace App\Http\Controllers;

use App\Models\ComputadorAutorizado;
use App\Models\Dispositivo;
use App\Models\MarcaOriginal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarcaWebController extends Controller
{
    /** Nombre de la cookie que identifica al terminal en esta PC. */
    private const COOKIE_TERMINAL = 'kairos_terminal';

    /**
     * Pantalla principal del terminal de marcas.
     * Identifica el computador por la marca (cookie) dejada en esta PC.
     */
    public function terminal(Request $request): View
    {
        $fingerprint = $request->cookie(self::COOKIE_TERMINAL);

        $computador = $fingerprint
            ? ComputadorAutorizado::where('fingerprint', $fingerprint)->first()
            : null;

        if (!$computador) {
            return view('marca-web.no-autorizado');
        }

        if (!$computador->autorizado) {
            return view('marca-web.pendiente', compact('computador'));
        }

        $computador->load('dispositivo.institucion');

        // Últimas 5 marcas registradas desde este dispositivo
        $ultimas = MarcaOriginal::where('id_dispositivo', $computador->id_dispositivo)
            ->with('usuario')
            ->orderByDesc('fecha_hora')
            ->limit(5)
            ->get();

        return view('marca-web.terminal', compact('computador', 'ultimas'));
    }

    /**
     * Registra una marca de entrada o salida.
     * Rate limit: 1 marca por usuario cada 5 minutos.
     */
    public function marcar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'documento' => ['required', 'string'],
        ]);

        // Verificar computador autorizado a partir de la marca dejada en esta PC
        $computador = $this->computadorDesdeCookie($request);

        if (!$computador) {
            return response()->json(['error' => 'Terminal no autorizado.'], 403);
        }

        // Verificar usuario
        $usuario = User::where('documento', $data['documento'])
            ->where('activo', true)
            ->first();

        if (!$usuario) {
            return response()->json(['error' => 'Documento no encontrado o usuario inactivo.'], 404);
        }

        // Rate limiting: 1 marca cada 5 minutos por usuario
        $key = 'marca_web_' . $usuario->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $segundos = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Debe esperar {$segundos} segundos antes de registrar otra marca.",
            ], 429);
        }
        RateLimiter::hit($key, 300); // ventana de 5 minutos

        $marca = MarcaOriginal::create([
            'id_usuario'     => $usuario->id,
            'id_dispositivo' => $computador->id_dispositivo,
            'fecha_hora'     => now(),
            'tipo_captura'   => 'web',
            'datos_raw'      => [
                'fingerprint' => $computador->fingerprint,
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ],
            'procesada'      => false,
        ]);

        return response()->json([
            'ok'     => true,
            'nombre' => $usuario->nombre_completo,
            'hora'   => $marca->fecha_hora->format('H:i:s'),
            'fecha'  => $marca->fecha_hora->isoFormat('dddd D [de] MMMM'),
        ]);
    }

    /**
     * Identifica un usuario por DNI sin registrar la marca todavía.
     * Devuelve nombre y si el dispositivo requiere contraseña.
     */
    public function identificar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'documento' => ['required', 'string'],
        ]);

        $computador = $this->computadorDesdeCookie($request, con: 'dispositivo');

        if (!$computador) {
            return response()->json(['error' => 'Terminal no autorizado.'], 403);
        }

        $usuario = User::where('documento', $data['documento'])
            ->where('activo', true)
            ->first();

        if (!$usuario) {
            return response()->json(['error' => 'Documento no encontrado o usuario inactivo.'], 404);
        }

        $requierePin = $computador->dispositivo->requiereContrasena();

        if ($requierePin && !$usuario->tienePinMarca()) {
            return response()->json([
                'error' => 'Este usuario no tiene PIN de marca configurado. Configure su PIN desde el perfil del sistema.',
            ], 422);
        }

        return response()->json([
            'ok'                  => true,
            'nombre'              => $usuario->nombre_completo,
            'requiere_contrasena' => $requierePin,
        ]);
    }

    /**
     * Valida credenciales y registra la marca.
     * Si el dispositivo requiere contraseña, la verifica antes de crear la marca.
     */
    public function confirmar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'documento' => ['required', 'string'],
            'password'  => ['nullable', 'string'],
        ]);

        $computador = $this->computadorDesdeCookie($request, con: 'dispositivo');

        if (!$computador) {
            return response()->json(['error' => 'Terminal no autorizado.'], 403);
        }

        $usuario = User::where('documento', $data['documento'])
            ->where('activo', true)
            ->first();

        if (!$usuario) {
            return response()->json(['error' => 'Documento no encontrado.'], 404);
        }

        if ($computador->dispositivo->requiereContrasena()) {
            if (empty($data['password'])) {
                return response()->json(['error' => 'Se requiere PIN de marca.'], 422);
            }
            if (!$usuario->tienePinMarca() || !Hash::check($data['password'], $usuario->pin_marca)) {
                return response()->json(['error' => 'PIN de marca incorrecto.'], 401);
            }
        }

        $key = 'marca_web_' . $usuario->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $segundos = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Debe esperar {$segundos} segundos antes de registrar otra marca.",
            ], 429);
        }
        RateLimiter::hit($key, 300);

        $marca = MarcaOriginal::create([
            'id_usuario'     => $usuario->id,
            'id_dispositivo' => $computador->id_dispositivo,
            'fecha_hora'     => now(),
            'tipo_captura'   => 'web',
            'datos_raw'      => [
                'fingerprint' => $computador->fingerprint,
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ],
            'procesada' => false,
        ]);

        return response()->json([
            'ok'     => true,
            'nombre' => $usuario->nombre_completo,
            'hora'   => $marca->fecha_hora->format('H:i:s'),
            'fecha'  => $marca->fecha_hora->isoFormat('dddd D [de] MMMM [de] Y'),
        ]);
    }

    /**
     * Solicita autorización para un nuevo computador.
     * El identificador del terminal se genera en el servidor y se deja
     * como cookie persistente en esta PC (nunca viaja por GET).
     */
    public function solicitarAutorizacion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_equipo'  => ['required', 'string', 'max:255'],
            'id_dispositivo' => ['required', 'integer', 'exists:dispositivos,id'],
        ]);

        // Si esta PC ya tiene una marca (p. ej. reenvía el formulario), la reutiliza.
        $fingerprint = $request->cookie(self::COOKIE_TERMINAL);
        $computador  = $fingerprint
            ? ComputadorAutorizado::where('fingerprint', $fingerprint)->first()
            : null;

        if (!$computador) {
            do {
                $fingerprint = Str::random(64);
            } while (ComputadorAutorizado::where('fingerprint', $fingerprint)->exists());

            $computador = ComputadorAutorizado::create([
                'fingerprint'    => $fingerprint,
                'id_dispositivo' => $data['id_dispositivo'],
                'nombre_equipo'  => $data['nombre_equipo'],
                'autorizado'     => false,
            ]);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Solicitud registrada. Un administrador deberá aprobarla.',
            'id'      => $computador->id,
        ])->withCookie(
            Cookie::forever(self::COOKIE_TERMINAL, $fingerprint, secure: $request->secure(), httpOnly: true)
        );
    }

    /**
     * Resuelve el computador autorizado a partir de la marca (cookie) dejada
     * en esta PC. Devuelve null si no hay marca o si el terminal no está autorizado.
     */
    private function computadorDesdeCookie(Request $request, ?string $con = null): ?ComputadorAutorizado
    {
        $fingerprint = $request->cookie(self::COOKIE_TERMINAL);

        if (!$fingerprint) {
            return null;
        }

        return ComputadorAutorizado::where('fingerprint', $fingerprint)
            ->where('autorizado', true)
            ->when($con, fn ($q) => $q->with($con))
            ->first();
    }
}
