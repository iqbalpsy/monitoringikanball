<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $device_id
 * @property numeric|null $temperature
 * @property numeric|null $ph
 * @property numeric|null $oxygen
 * @property \Illuminate\Support\Carbon $recorded_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device $device
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData inTimeRange($start, $end)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData latest($limit = 10)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereOxygen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData wherePh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereRecordedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SensorData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';

    protected $fillable = [
        'device_id',
        'ph',
        'temperature',
        'oxygen',
        'voltage',
        'turbidity',
        'raw_data',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'ph' => 'decimal:2',
            'temperature' => 'decimal:2',
            'oxygen' => 'decimal:2',
            'voltage' => 'decimal:2',
            'turbidity' => 'decimal:2',
            'raw_data' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * The device that recorded this data
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope for getting data from specific time range
     */
    public function scopeInTimeRange($query, $start, $end)
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }

    /**
     * Scope for getting latest data
     */
    public function scopeLatest($query, $limit = 10)
    {
        return $query->orderBy('recorded_at', 'desc')->limit($limit);
    }

    /**
     * Check if pH level is in normal range
     */
    public function isPhNormal(): bool
    {
        return $this->ph >= 6.5 && $this->ph <= 8.5;
    }

    /**
     * Check if temperature is in normal range (for tropical fish)
     */
    public function isTemperatureNormal(): bool
    {
        return $this->temperature >= 24 && $this->temperature <= 30;
    }

    /**
     * Check if oxygen level is adequate
     */
    public function isOxygenAdequate(): bool
    {
        return $this->oxygen >= 5; // Minimum 5 mg/L
    }
}
