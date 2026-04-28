<?php

namespace App\Listeners;

use App\Models\Institucion;
use Illuminate\Auth\Events\Login;

class EstablecerInstitucionPorDefecto
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user->hasRole('Administrador General')) {
            // Admin General: primera institución raíz activa
            $inst = Institucion::activas()->raices()->orderBy('nombre')->first();
        } else {
            // Obtener IDs de instituciones con rol vigente
            $ids = $user->rolesInstitucion()
                ->vigente()
                ->pluck('id_institucion')
                ->unique()
                ->filter()
                ->toArray();

            if (empty($ids)) {
                return;
            }

            // Cargar instituciones con su padre para calcular profundidad
            $instituciones = Institucion::whereIn('id', $ids)
                ->activas()
                ->with('padre.padre.padre')
                ->get();

            // Ordenar por profundidad jerárquica ascendente (raíz = 0)
            $inst = $instituciones->sortBy(function (Institucion $i): int {
                $nivel   = 0;
                $current = $i;
                while ($current->id_institucion_padre !== null && $nivel < 10) {
                    $current = $current->padre;
                    if (! $current) break;
                    $nivel++;
                }
                return $nivel;
            })->first();
        }

        if ($inst) {
            session(['institucion_activa_id' => $inst->id]);
        }
    }
}
