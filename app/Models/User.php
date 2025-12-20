<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Champs assignables
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'avatar',
        'google_id',

        // Profil
        'gender',
        'bio',
        'city',
        'country',

        // Worker
        'profession',
        'skills',
        'experience_years',
        'availability',

        // Manager
        'company_name',
        'company_activity',
        'company_verified',
    ];

    /**
     * Champs cachés
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts automatiques
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'skills'            => 'array',
        'availability'      => 'boolean',
        'company_verified'  => 'boolean',
    ];

    /* =========================
        Helpers métier
    ========================== */

    public function isWorker(): bool
    {
        return $this->role === 'worker';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function hasGoogleAccount(): bool
    {
        return !is_null($this->google_id);
    }
}
