<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use App\Models\Ciudad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    /** Retorna los estados/provincias de un país (para cascading select). */
    public function estados(Request $request): JsonResponse
    {
        $estados = Estado::where('id_pais', (int) $request->id_pais)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($estados);
    }

    /** Retorna las ciudades/departamentos de un estado (para cascading select). */
    public function ciudades(Request $request): JsonResponse
    {
        $ciudades = Ciudad::where('id_estado', (int) $request->id_estado)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($ciudades);
    }
}
