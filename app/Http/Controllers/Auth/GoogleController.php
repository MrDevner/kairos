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
     * Maneja el callback de Google OAuth.
     *
     * Orden de resolución:
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

        $usuario->assignRole('usuario');

        return $usuario;
    }
}
