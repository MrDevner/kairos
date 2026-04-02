<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function mostrarFormulario(): View
    {
        return view('auth.contrasena.solicitar');
    }

    public function enviarEnlace(Request $request): RedirectResponse
    {
        $request->validate([
            'documento' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('documento', $request->documento)
            ->where('activo', true)
            ->first();

        // Siempre mostramos el mismo mensaje para no revelar si el documento existe
        if (! $usuario || ! $usuario->email) {
            return back()
                ->with('status', 'Si el documento está registrado, recibirás un correo con el enlace.')
                ->withInput();
        }

        $status = Password::sendResetLink(['email' => $usuario->email]);

        return back()->with('status', 'Si el documento está registrado, recibirás un correo con el enlace.');
    }
}
