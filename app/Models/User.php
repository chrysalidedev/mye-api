<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        
        // Géolocalisation
        'latitude',
        'longitude',
        'location_updated_at',
        
        // FCM
        'fcm_token',
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
        'latitude'          => 'decimal:8',
        'longitude'         => 'decimal:8',
        'location_updated_at' => 'datetime',
    ];

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

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

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
