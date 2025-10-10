<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
