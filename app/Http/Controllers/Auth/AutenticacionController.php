<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AutenticacionController extends Controller
{
    public function mostrarLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/home');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'documento' => ['required', 'string'],
            'password'  => ['required', 'string'],
        ]);

        if (! Auth::attempt(
            ['documento' => $credenciales['documento'], 'password' => $credenciales['password'], 'activo' => true],
            $request->boolean('recordar')
        )) {
            return back()
                ->withErrors(['documento' => 'Credenciales incorrectas o cuenta desactivada.'])
                ->onlyInput('documento');
        }

        $request->session()->regenerate();

        return redirect()->intended('/home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
