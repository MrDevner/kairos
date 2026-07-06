<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirige al usuario a la pantalla de autenticación de Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Inicia el flujo OAuth para vincular Google a la cuenta ya autenticada.
     */
    public function vincular(): RedirectResponse
    {
        session(['vincular_para_usuario' => auth()->id()]);
        return Socialite::driver('google')->redirect();
    }

    /**
     * Desvincula Google de la cuenta del usuario autenticado.
     */
    public function desvincular(): RedirectResponse
    {
        $usuario = auth()->user();

        if (! $usuario->password) {
            return back()->with('error', 'No podés desvincular Google sin tener una contraseña configurada.');
        }

        $usuario->update(['google_id' => null]);
        return back()->with('success', 'Cuenta de Google desvinculada correctamente.');
    }

    /**
     * Maneja el callback de Google OAuth.
     *
     * Orden de resolución:
     *  0. Si hay sesión "vincular_para_usuario" → solo vincular a la cuenta actual.
     *  1. google_id ya vinculado → login directo.
     *  2. Email coincide con cuenta existente → vincular y hacer login.
     *  3. Usuario nuevo → crear con datos de Google y hacer login.
     */
    public function callback(): RedirectResponse
    {
        try {
            $gUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No se pudo autenticar con Google. Intentá de nuevo.']);
        }

        // 0. Modo vinculación desde perfil
        $vincularParaId = session()->pull('vincular_para_usuario');
        if ($vincularParaId && auth()->check() && auth()->id() === $vincularParaId) {
            if (Usuario::where('google_id', $gUser->getId())->where('id', '!=', $vincularParaId)->exists()) {
                return redirect()->route('perfil')->with('error', 'Esa cuenta de Google ya está vinculada a otro usuario.');
            }
            auth()->user()->update(['google_id' => $gUser->getId()]);
            return redirect()->route('perfil')->with('success', 'Cuenta de Google vinculada correctamente.');
        }

        // 1. Buscar por google_id existente
        $usuario = Usuario::where('google_id', $gUser->getId())->first();

        if (! $usuario) {
            // 2. Email coincide → vincular cuenta
            if ($gUser->getEmail()) {
                $usuario = Usuario::where('email', $gUser->getEmail())->first();

                if ($usuario) {
                    $usuario->update([
                        'google_id' => $gUser->getId(),
                        'foto'      => $usuario->foto ?? $gUser->getAvatar(),
                    ]);
                }
            }

            // 3. Crear nuevo usuario con datos de Google
            if (! $usuario) {
                $usuario = $this->crearDesdeGoogle($gUser);
            }
        }

        if (! $usuario->activo) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Su cuenta está desactivada. Contacte al administrador.']);
        }

        Auth::login($usuario, remember: true);
        request()->session()->regenerate();

        return redirect()->intended('/home');
    }

    /**
     * Crea un nuevo usuario a partir de los datos de Google.
     * El campo `documento` se inicializa con un placeholder hasta que
     * el administrador complete el perfil.
     */
    private function crearDesdeGoogle(\Laravel\Socialite\Contracts\User $gUser): Usuario
    {
        $nombre = trim($gUser->getName() ?? '');
        $partes  = explode(' ', $nombre, 2);

        $usuario = Usuario::create([
            'nombres'   => $partes[0],
            'apellidos' => $partes[1] ?? $partes[0],
            'documento' => 'GOOGLE-' . $gUser->getId(),
            'email'     => $gUser->getEmail(),
            'google_id' => $gUser->getId(),
            'foto'      => $gUser->getAvatar(),
            'activo'    => true,
        ]);

        return $usuario;
    }
}
