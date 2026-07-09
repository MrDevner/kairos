<?php

namespace App\Policies;

use App\Models\Aviso;
use App\Models\User;

class AvisoPolicy
{
    /**
     * Puede ver un aviso si:
     * - Es el propio usuario que avisó
     * - Tiene rol 'Departamento Personal' en la institución del aviso
     * - Es jefe vigente de la dependencia del usuario que avisó
     */
    public function ver(User $usuario, Aviso $aviso): bool
    {
        if ($usuario->id === $aviso->id_usuario) {
            return true;
        }

        if ($usuario->tieneRolEnInstitucion('Departamento Personal', $aviso->id_institucion)) {
            return true;
        }

        // Verificar si es jefe vigente de la dependencia del aviso
        $aviso->loadMissing('designacion');
        $idDependencia = $aviso->designacion?->id_dependencia;

        if ($idDependencia === null) {
            return false;
        }

        return $usuario->jefaturas()
            ->vigente()
            ->where('id_dependencia', $idDependencia)
            ->exists();
    }

    /**
     * Puede crear avisos para un usuario si:
     * - Es personal del Departamento Personal en la institución
     * - O la institución permite avisos_usuario y es el propio usuario
     */
    public function crear(User $usuario, int $idInstitucion): bool
    {
        if ($usuario->tieneRolEnInstitucion('Departamento Personal', $idInstitucion)) {
            return true;
        }

        $institucion = \App\Models\Institucion::find($idInstitucion);
        if ($institucion && $institucion->getConfig('permite_avisos_usuario')) {
            return true;
        }

        return false;
    }

    /**
     * Solo el Departamento Personal puede editar o eliminar avisos.
     */
    public function editar(User $usuario, Aviso $aviso): bool
    {
        return $usuario->tieneRolEnInstitucion('Departamento Personal', $aviso->id_institucion);
    }

    public function eliminar(User $usuario, Aviso $aviso): bool
    {
        return $this->editar($usuario, $aviso);
    }
}
