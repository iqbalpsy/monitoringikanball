<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $device_id
 * @property int $user_id
 * @property string $action
 * @property array<array-key, mixed>|null $parameters
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $executed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Device $device
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl executed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereExecutedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceControl whereUserId($value)
 * @mixin \Eloquent
 */
class DeviceControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'user_id',
        'action',
        'parameters',
        'previous_state',
        'new_state',
        'status',
        'notes',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'previous_state' => 'array',
            'new_state' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    /**
     * The device being controlled
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * The user who performed the control
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending controls
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for executed controls
     */
    public function scopeExecuted($query)
    {
        return $query->where('status', 'executed');
    }

    /**
     * Mark control as executed
     */
    public function markAsExecuted($newState = null)
    {
        $this->update([
            'status' => 'executed',
            'executed_at' => now(),
            'new_state' => $newState ?? $this->new_state,
        ]);
    }

    /**
     * Mark control as failed
     */
    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason ? $this->notes . "\nFailed: " . $reason : $this->notes,
        ]);
    }
}
