<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends BaseModel
{
    protected $table = 'dispositivos';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'id_institucion',
        'tipo',
        'modo_conexion',
        'ip_address',
        'configuracion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'configuracion' => 'array',
            'activo'        => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }

    public function marcasOriginales(): HasMany
    {
        return $this->hasMany(MarcaOriginal::class, 'id_dispositivo');
    }

    public function computadoresAutorizados(): HasMany
    {
        return $this->hasMany(ComputadorAutorizado::class, 'id_dispositivo');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDeInstitucion(Builder $query, int $idInstitucion): Builder
    {
        return $query->where('id_institucion', $idInstitucion);
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function esWeb(): bool        { return $this->tipo === 'web'; }
    public function esBiometrico(): bool { return $this->tipo === 'biometrico'; }
    public function requiereImportacion(): bool { return $this->modo_conexion === 'importacion'; }

    /** Solicitar contraseña al marcar (activado por defecto). */
    public function requiereContrasena(): bool
    {
        return (bool) (($this->configuracion ?? [])['solicitar_contrasena'] ?? true);
    }
}
