<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketCategoria extends BaseModel
{
    use SoftDeletes;

    protected $table = 'ticket_categorias';

    protected $fillable = ['nombre', 'slug', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** @var Collection<int, string>|null */
    private static ?Collection $cache = null;

    /** Nombres de las categorías activas, cacheados en memoria durante el request. */
    public static function categorias(): Collection
    {
        return self::$cache ??= self::where('activo', true)->orderBy('nombre')->pluck('nombre');
    }

    public static function limpiarCache(): void
    {
        self::$cache = null;
    }

    public static function generarSlug(string $nombre): string
    {
        return Str::slug($nombre);
    }
}
