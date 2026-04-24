<?php

namespace App\Models;

use App\Enums\RolUsuario;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'area_id',
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

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function areasJefatura(): HasMany
    {
        return $this->hasMany(Area::class, 'subdirector_id');
    }

    public function incidenciasRevisadas(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'revisado_por');
    }

    // ─── Helpers de rol ──────────────────────────────────────────────────────

    public function esAdmin(): bool
    {
        return $this->rol === RolUsuario::Admin;
    }

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
}
