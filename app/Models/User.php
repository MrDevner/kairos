<?php

namespace App\Models;

use App\Permisos\ContenedorDePermisos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'apellidos',
        'nombres',
        'documento',
        'email',
        'telefono',
        'domicilio',
        'id_ciudad_domicilio',
        'sexo',
        'nacimiento',
        'id_estado_nacimiento',
        'id_pais_nacimiento',
        'foto',
        'password',
        'pin_marca',
        'google_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'pin_marca',
        'token_recuerdo',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'activo'     => 'boolean',
            'password'   => 'hashed',
            'pin_marca'  => 'hashed',
            'nacimiento' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $usuario) {
            if (empty($usuario->token)) {
                do {
                    $token = Str::random(60);
                } while (static::where('token', $token)->exists());
                $usuario->token = $token;
            }
        });
    }

    // --- Overrides de Authenticatable para columnas en español ---

    public function getRememberTokenName(): string
    {
        return 'token_recuerdo';
    }

    // --- Relaciones de ubicación ---

    public function ciudadDomicilio(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad_domicilio');
    }

    public function estadoNacimiento(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado_nacimiento');
    }

    public function paisNacimiento(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'id_pais_nacimiento');
    }

    // --- Relaciones ---

    public function rolesInstitucion(): HasMany
    {
        return $this->hasMany(RolInstitucionUsuario::class, 'id_usuario');
    }

    public function jefaturas(): HasMany
    {
        return $this->hasMany(Jefatura::class, 'id_usuario');
    }

    public function designaciones(): HasMany
    {
        return $this->hasMany(Designacion::class, 'id_usuario');
    }

    public function declaracionesJuradas(): HasMany
    {
        return $this->hasMany(DeclaracionJurada::class, 'id_usuario');
    }

    public function avisos(): HasMany
    {
        return $this->hasMany(Aviso::class, 'id_usuario');
    }

    // --- Helpers de institución ---

    /**
     * Devuelve las asignaciones de rol vigentes para una institución.
     */
    public function rolesVigentesEnInstitucion(int $institucionId): Collection
    {
        return $this->rolesInstitucion()
            ->vigente()
            ->deInstitucion($institucionId)
            ->with('rolInstitucion')
            ->get();
    }

    /**
     * Verifica si el usuario tiene alguno de los roles indicados en la institución.
     */
    public function tieneRolEnInstitucion(string|array $roles, int $institucionId): bool
    {
        $roles = (array) $roles;

        return $this->rolesInstitucion()
            ->vigente()
            ->deInstitucion($institucionId)
            ->whereHas('rolInstitucion', fn (Builder $q) => $q->whereIn('nombre', $roles))
            ->exists();
    }

    /**
     * Nombres de los roles globales vigentes del usuario (id_institucion null).
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function nombresRolesGlobales(): \Illuminate\Support\Collection
    {
        return $this->rolesInstitucion()
            ->vigente()
            ->whereNull('id_institucion')
            ->with('rolInstitucion')
            ->get()
            ->pluck('rolInstitucion.nombre')
            ->filter()
            ->values();
    }

    /**
     * Verifica si el usuario puede realizar una acción sobre un módulo en la institución.
     */
    public function puedeEnInstitucion(string $accion, string $modulo, int $institucionId): bool
    {
        return $this->rolesInstitucion()
            ->vigente()
            ->deInstitucion($institucionId)
            ->whereHas('rolInstitucion.permisos', fn (Builder $q) =>
                $q->where('modulo', $modulo)->where("puede_{$accion}", true)
            )
            ->exists();
    }

    /**
     * Permisos efectivos del usuario: unión (OR) de los ContenedorDePermisos de
     * todos sus roles vigentes, globales (comodín administrador incluido) más
     * los de la institución indicada (por defecto, la institución activa en sesión).
     */
    public function permisos(?int $institucionId = null): ContenedorDePermisos
    {
        $institucionId ??= session('institucion_activa_id') ? (int) session('institucion_activa_id') : null;

        $asignaciones = $this->rolesInstitucion()
            ->vigente()
            ->globalODeInstitucion($institucionId)
            ->with('rolInstitucion.permisos')
            ->get();

        $contenedor = ContenedorDePermisos::vacio();

        foreach ($asignaciones as $asignacion) {
            if ($asignacion->rolInstitucion) {
                $contenedor->unirPermisos($asignacion->rolInstitucion->contenedorDePermisos());
            }
        }

        return $contenedor;
    }

    // --- Helpers ---

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellidos}, {$this->nombres}";
    }

    public function tienePassword(): bool
    {
        return ! is_null($this->getAttributes()['password'] ?? null);
    }

    public function tienePinMarca(): bool
    {
        return ! is_null($this->getAttributes()['pin_marca'] ?? null);
    }

    public function tieneGoogle(): bool
    {
        return ! is_null($this->google_id);
    }

    // --- Activitylog ---

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
