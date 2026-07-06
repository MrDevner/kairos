<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstitucionActivaController extends Controller
{
    public function cambiar(Request $request): RedirectResponse
    {
        $request->validate([
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id'],
        ]);

        $id   = (int) $request->id_institucion;
        $user = $request->user();

        // Verifica que el usuario tenga acceso a esa institución
        $tieneAcceso = $user->permisos()->administrador()->tieneTodosLosPermisos()
            || $user->rolesInstitucion()->vigente()->where('id_institucion', $id)->exists();

        abort_unless($tieneAcceso, 403, 'No tenés acceso a esa institución.');

        session(['institucion_activa_id' => $id]);

        return redirect()->intended(route('home'));
    }
}
