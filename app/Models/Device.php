<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $device_id
 * @property string $name
 * @property string|null $location
 * @property string|null $description
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $authorizedUsers
 * @property-read int|null $authorized_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeviceControl> $controls
 * @property-read int|null $controls_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\SensorData|null $latestSensorData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SensorData> $sensorData
 * @property-read int|null $sensor_data_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_id',
        'location',
        'description',
        'status',
        'settings',
        'created_by',
        'is_active',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * The user who created this device
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sensor data from this device
     */
    public function sensorData()
    {
        return $this->hasMany(SensorData::class);
    }

    /**
     * Latest sensor data
     */
    public function latestSensorData()
    {
        return $this->hasOne(SensorData::class)->latest('recorded_at');
    }

    /**
     * Control commands for this device
     */
    public function controls()
    {
        return $this->hasMany(DeviceControl::class);
    }

    /**
     * Users who have access to this device
     */
    public function authorizedUsers()
    {
        return $this->belongsToMany(User::class, 'user_device_access')
                    ->withPivot(['can_view_data', 'can_control', 'granted_at', 'expires_at'])
                    ->withTimestamps();
    }

    /**
     * Check if device is online
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Check if device is offline
     */
    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    /**
     * Update last seen timestamp
     */
    public function updateLastSeen()
    {
        $this->update(['last_seen_at' => now()]);
    }
}
