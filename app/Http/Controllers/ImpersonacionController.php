<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Permisos\Permisos;
use Illuminate\Http\RedirectResponse;

/**
 * Permite a un administrador (comodín "*") ver el sistema como otro usuario.
 *
 * La autorización para iniciar/detener siempre se chequea contra el usuario
 * real (Permisos::delUsuarioReal()), nunca contra el personificado, para que
 * un admin no pueda auto-otorgarse privilegios ocultándose detrás de una
 * personificación.
 */
class ImpersonacionController extends Controller
{
    public function iniciar(User $usuario): RedirectResponse
    {
        abort_unless(
            Permisos::delUsuarioReal()->administrador()->tieneTodosLosPermisos(),
            403,
            'Solo un administrador puede personificar a otro usuario.'
        );

        abort_if($usuario->id === Permisos::usuarioReal()->id, 422, 'No podés personificarte a vos mismo.');

        Permisos::iniciarPersonificacion($usuario);

        return redirect()->route('home')
            ->with('success', "Ahora estás viendo el sistema como {$usuario->nombre_completo}.");
    }

    public function detener(): RedirectResponse
    {
        Permisos::detenerPersonificacion();

        return redirect()->route('home')->with('success', 'Volviste a tu usuario real.');
    }
}
