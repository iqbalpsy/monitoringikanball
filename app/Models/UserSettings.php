<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property numeric $temp_min
 * @property numeric $temp_max
 * @property numeric $ph_min
 * @property numeric $ph_max
 * @property numeric $oxygen_min
 * @property numeric $oxygen_max
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereOxygenMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereOxygenMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings wherePhMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings wherePhMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereTempMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereTempMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUserId($value)
 * @mixin \Eloquent
 */
class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'temp_min',
        'temp_max',
        'ph_min',
        'ph_max',
        'oxygen_min',
        'oxygen_max',
    ];

    protected $casts = [
        'temp_min' => 'decimal:2',
        'temp_max' => 'decimal:2',
        'ph_min' => 'decimal:2',
        'ph_max' => 'decimal:2',
        'oxygen_min' => 'decimal:2',
        'oxygen_max' => 'decimal:2',
    ];

    /**
     * Get the user that owns the settings
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a temperature value is within thresholds
     */
    public function isTempNormal($value)
    {
        return $value >= $this->temp_min && $value <= $this->temp_max;
    }

    /**
     * Check if a pH value is within thresholds
     */
    public function isPhNormal($value)
    {
        return $value >= $this->ph_min && $value <= $this->ph_max;
    }

    /**
     * Check if an oxygen value is within thresholds
     */
    public function isOxygenNormal($value)
    {
        return $value >= $this->oxygen_min && $value <= $this->oxygen_max;
    }
}
