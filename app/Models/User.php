<?php

namespace App\Models;

use App\Enums\RolUsuario;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'nombre',
        'email',
        'numero_empleado',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => RolUsuario::class,
            'activo' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'area_user')
            ->withPivot('es_jefe')
            ->withTimestamps();
    }

    public function areasJefatura(): BelongsToMany
    {
        return $this->areas()->wherePivot('es_jefe', true);
    }

    public function getAreaAttribute(): ?Area
    {
        $areaId = $this->area_id; // Ahora usa el accessor corregido ↑

        if ($areaId === null) {
            return null;
        }

        return $this->areas()->where('areas.id', $areaId)->first();
    }

    public function incidenciasRevisadas(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'revisado_por');
    }

    // ─── Helpers de rol ──────────────────────────────────────────────────────

    public function esJefeInmediato(): bool
    {
        return $this->rol === RolUsuario::JefeInmediato;
    }

    public function esCapitalHumano(): bool
    {
        return $this->rol === RolUsuario::CapitalHumano;
    }

    public function esSubdirector(): bool
    {
        return $this->rol === RolUsuario::Subdirector;
    }

    public function tieneRol(RolUsuario ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function getAreaIdAttribute(): ?int
{
    if (! $this->esJefeInmediato()) {
        return null;
    }

    // En contexto web: leer el área seleccionada al hacer login
    try {
        if (! app()->runningInConsole()) {
            $sessionAreaId = session('area_id');
            if ($sessionAreaId && $this->isJefeOfArea((int) $sessionAreaId)) {
                return (int) $sessionAreaId;
            }
        }
    } catch (\Throwable) {
        // Sin sesión activa (CLI, tests, etc.) → fallback
    }

    // Fallback: primera área asignada (orden de la DB)
    return $this->areasJefatura()->value('areas.id');
}

    public function primerAreaJefaturaId(): ?int
    {
        if (! $this->esJefeInmediato()) {
            return null;
        }

        return $this->areasJefatura()->value('areas.id');
    }

    public function tieneArea(): bool
    {
        return $this->esJefeInmediato() && $this->areasJefatura()->exists();
    }

    public function isJefeOfArea(int $areaId): bool
    {
        if (! $this->esJefeInmediato()) {
            return false;
        }

        return $this->areas()->wherePivot('es_jefe', true)->where('areas.id', $areaId)->exists();
    }
}
