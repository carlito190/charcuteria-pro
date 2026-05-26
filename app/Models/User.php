<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'role', // <-- Añadido el rol
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

        // --- HELPERS DE ROLES ---

    /**
     * Nivel 1: Súper Admin Global (Tú)
     * Puede ver y auditar todas las compras y ventas de todas las sedes.
     */
    public function isGlobalAdmin()
    {
        return $this->role === 'admin_global';
    }

/**
 * Nivel 2: Administrador de Sede
 * Gestiona, ve historiales y hace cierres, pero ESTRICTAMENTE en su sucursal asignada.
 */
    public function isBranchAdmin()
    {
        return $this->role === 'admin_branch';
    }

    /**
     * Nivel 3: Trabajador / Cajero por Sede
     * Solo factura o registra, tiene acceso restringido a ver historiales generales.
     */
    public function isBranchWorker()
    {
        return $this->role === 'worker_branch';
    }
}
