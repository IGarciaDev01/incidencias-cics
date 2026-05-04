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
        return $this->areas()->first();
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

        $jefatura = $this->areasJefatura()->first();

        return $jefatura?->id;
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
