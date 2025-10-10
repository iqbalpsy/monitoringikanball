<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Devices created by this user (for admin)
     */
    public function createdDevices()
    {
        return $this->hasMany(Device::class, 'created_by');
    }

    /**
     * Device controls performed by this user
     */
    public function deviceControls()
    {
        return $this->hasMany(DeviceControl::class);
    }

    /**
     * Devices this user has access to
     */
    public function accessibleDevices()
    {
        return $this->belongsToMany(Device::class, 'user_device_access')
                    ->withPivot(['can_view_data', 'can_control', 'granted_at', 'expires_at'])
                    ->withTimestamps();
    }

    /**
     * Device access grants given by this user (for admin)
     */
    public function grantedAccess()
    {
        return $this->hasMany(UserDeviceAccess::class, 'granted_by');
    }
}
