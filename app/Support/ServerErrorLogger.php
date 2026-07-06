<?php

namespace App\Support;

use App\Models\ErrorServidor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Captura, deduplica y persiste excepciones no controladas (500) para el
 * panel de administración. Nunca debe romper la respuesta al usuario: todo
 * el cuerpo de log() corre en un try/catch silencioso.
 */
class ServerErrorLogger
{
    /** Claves de parámetros que nunca se persisten en texto plano. */
    private const CLAVES_SENSIBLES = [
        'password', 'password_confirmation', 'token', 'secret',
        'api_key', 'apikey', 'cvv', 'card_number', 'tarjeta',
    ];

    /** Errores que no son fallas reales de servidor: no se registran. */
    public static function shouldSkip(Throwable $e): bool
    {
        if ($e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof ModelNotFoundException) {
            return true;
        }

        return $e instanceof HttpExceptionInterface && $e->getStatusCode() < 500;
    }

    public static function log(Throwable $e, Request $request): void
    {
        try {
            if (self::shouldSkip($e)) {
                return;
            }

            $fingerprint = hash(
                'sha256',
                get_class($e).'|'.$e->getFile().'|'.$e->getLine().'|'.$e->getMessage()
            );

            $correlationId = (string) Str::uuid();

            $error = ErrorServidor::activos()->where('huella_error', $fingerprint)->first();

            if ($error) {
                $error->id_correlacion = $correlationId;
                $error->cantidad_ocurrencias += 1;
                $error->ultima_ocurrencia_en = now();
                $error->save();
            } else {
                ErrorServidor::create([
                    'id_correlacion'       => $correlationId,
                    'endpoint'             => (string) $request->fullUrl(),
                    'metodo_http'          => $request->method(),
                    'id_usuario'           => $request->user()?->id,
                    'direccion_ip'         => $request->ip(),
                    'agente_usuario'       => (string) $request->userAgent(),
                    'parametros_solicitud' => self::sanitizar($request->all()),
                    'mensaje_error'        => $e->getMessage(),
                    'clase_error'          => get_class($e),
                    'traza_pila'           => $e->getTraceAsString(),
                    'archivo'              => $e->getFile(),
                    'linea'                => $e->getLine(),
                    'huella_error'         => $fingerprint,
                    'cantidad_ocurrencias' => 1,
                    'estado'               => 'abierto',
                    'ultima_ocurrencia_en' => now(),
                ]);
            }

            $request->attributes->set('correlation_id', $correlationId);
        } catch (Throwable) {
            // El logger nunca puede romper la respuesta al usuario.
        }
    }

    private static function sanitizar(array $params): array
    {
        $resultado = [];

        foreach ($params as $clave => $valor) {
            if (is_array($valor)) {
                $resultado[$clave] = self::sanitizar($valor);
                continue;
            }

            $esSensible = collect(self::CLAVES_SENSIBLES)
                ->contains(fn ($clave_sensible) => str_contains(strtolower((string) $clave), $clave_sensible));

            $resultado[$clave] = $esSensible ? '[REDACTED]' : $valor;
        }

        return $resultado;
    }
}
