<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property int $device_id
 * @property bool $can_view_data
 * @property bool $can_control
 * @property int|null $granted_by
 * @property \Illuminate\Support\Carbon $granted_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device $device
 * @property-read \App\Models\User|null $grantedBy
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereCanControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereCanViewData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereGrantedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereGrantedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeviceAccess whereUserId($value)
 * @mixin \Eloquent
 */
class UserDeviceAccess extends Model
{
    use HasFactory;

    protected $table = 'user_device_access';

    protected $fillable = [
        'user_id',
        'device_id',
        'granted_by',
        'can_view_data',
        'can_control',
        'granted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'can_view_data' => 'boolean',
            'can_control' => 'boolean',
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The user who has access
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The device being accessed
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * The admin who granted access
     */
    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if access is still valid
     */
    public function isValid(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Scope for active access
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Grant view access to user
     */
    public static function grantViewAccess($userId, $deviceId, $grantedBy)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'device_id' => $deviceId],
            [
                'granted_by' => $grantedBy,
                'can_view_data' => true,
                'can_control' => false,
                'granted_at' => now(),
            ]
        );
    }

    /**
     * Grant control access to user (usually for admin only)
     */
    public static function grantControlAccess($userId, $deviceId, $grantedBy)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'device_id' => $deviceId],
            [
                'granted_by' => $grantedBy,
                'can_view_data' => true,
                'can_control' => true,
                'granted_at' => now(),
            ]
        );
    }
}
