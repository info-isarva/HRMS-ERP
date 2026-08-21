<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRead extends Model
{
    protected $fillable = [
        'notification_id',
        'user_id',
        'read_at'
    ];
    
    protected $casts = [
        'read_at' => 'datetime'
    ];
    
    /**
     * Get the notification that was read
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(ManualNotification::class, 'notification_id');
    }
    
    /**
     * Get the user who read the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
