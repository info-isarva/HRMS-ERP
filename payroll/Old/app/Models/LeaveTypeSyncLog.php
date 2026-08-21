<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveTypeSyncLog extends Model
{
    protected $table = 'leave_type_sync_logs';

    protected $fillable = [
        'sync_type',
        'financial_year',
        'total_synced',
        'success_count',
        'error_count',
        'errors',
        'sync_details',
        'started_at',
        'completed_at',
        'status',
        'triggered_by',
    ];

    protected $casts = [
        'errors' => 'array',
        'sync_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who triggered this sync
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'triggered_by');
    }

    /**
     * Scope to get completed syncs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get running syncs
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Get sync duration in seconds
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * Check if sync was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed' && $this->error_count === 0;
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_synced === 0) {
            return 0;
        }

        return round(($this->success_count / $this->total_synced) * 100, 2);
    }
}