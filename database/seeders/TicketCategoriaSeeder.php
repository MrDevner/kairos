<?php

namespace Database\Seeders;

use App\Models\TicketCategoria;
use Illuminate\Database\Seeder;

class TicketCategoriaSeeder extends Seeder
{
    private const CATEGORIAS = [
        'Error del sistema',
        'Consulta',
        'Solicitud de acceso',
        'Otro',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIAS as $nombre) {
            TicketCategoria::firstOrCreate(
                ['slug' => TicketCategoria::generarSlug($nombre)],
                ['nombre' => $nombre, 'activo' => true]
            );
        }
    }
}
