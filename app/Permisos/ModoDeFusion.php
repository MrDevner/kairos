<?php

namespace App\Permisos;

/** Modo de fusión al agregar un permiso a un ContenedorDePermisos que ya tiene ese módulo. */
enum ModoDeFusion
{
    /** Descarta el valor anterior y lo reemplaza por el nuevo. */
    case REEMPLAZAR;

    /** OR flag a flag. Modo por defecto: el que se usa al combinar los roles de un usuario. */
    case UNIR;

    /** AND flag a flag (permiso más restrictivo). */
    case INTERSECTAR;
}
