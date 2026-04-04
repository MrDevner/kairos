<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    protected $table = 'estados';

    protected $fillable = ['nombre', 'id_pais'];

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'id_pais');
    }

    public function ciudades(): HasMany
    {
        return $this->hasMany(Ciudad::class, 'id_estado');
    }
}
