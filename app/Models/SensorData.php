<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';

    protected $fillable = [
        'device_id',
        'ph_level',
        'temperature',
        'oxygen_level',
        'turbidity',
        'raw_data',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'ph_level' => 'decimal:2',
            'temperature' => 'decimal:2',
            'oxygen_level' => 'decimal:2',
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
        return $this->ph_level >= 6.5 && $this->ph_level <= 8.5;
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
        return $this->oxygen_level >= 5; // Minimum 5 mg/L
    }
}
