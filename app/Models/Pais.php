<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    protected $table = 'paises';

    protected $fillable = ['nombre', 'nombre_ingles', 'iso2', 'iso3', 'iso_numerico'];

    public function estados(): HasMany
    {
        return $this->hasMany(Estado::class, 'id_pais');
    }
}
