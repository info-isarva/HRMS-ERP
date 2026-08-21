<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ManualNotification extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title',
        'message',
        'priority',
        'status',
        'target_type',
        'target_departments',
        'target_employees',
        'start_date',
        'end_date',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_end_date',
        'show_in_header',
        'send_email',
        'send_sms',
        'icon',
        'color',
        'created_by',
        'updated_by'
    ];
    
    protected $casts = [
        'target_departments' => 'array',
        'target_employees' => 'array',
        'recurrence_days' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'recurrence_end_date' => 'date',
        'show_in_header' => 'boolean',
        'send_email' => 'boolean',
        'send_sms' => 'boolean'
    ];
    
    protected $dates = ['deleted_at'];
    
    /**
     * Get the reads for this notification
     */
    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class, 'notification_id');
    }
    
    /**
     * Get the creator of this notification
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the updater of this notification
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Check if notification is active right now
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        
        if ($this->status !== 'active') {
            return false;
        }
        
        if ($now->lt($this->start_date)) {
            return false;
        }
        
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if notification is read by specific user
     */
    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }
    
    /**
     * Mark notification as read by specific user
     */
    public function markAsReadBy(int $userId): void
    {
        $this->reads()->updateOrCreate(
            ['user_id' => $userId],
            ['read_at' => Carbon::now()]
        );
    }
    
    /**
     * Get targeted users based on targeting settings
     */
    public function getTargetedUsers()
    {
        $query = User::query();
        
        switch ($this->target_type) {
            case 'all':
                // All active users
                break;
                
            case 'department':
                if ($this->target_departments) {
                    $query->whereHas('employee', function($q) {
                        $q->whereIn('department', $this->target_departments);
                    });
                }
                break;
                
            case 'specific_employees':
                if ($this->target_employees) {
                    // Use employee table id (primary key) instead of employee_id (manual string)
                    $query->whereHas('employee', function($q) {
                        $q->whereIn('id', $this->target_employees);
                    });
                }
                break;
        }
        
        return $query->where('status', 'Active')->get();
    }
    
    /**
     * Scope for active notifications
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        
        return $query->where('status', 'active')
                     ->where('start_date', '<=', $now)
                     ->where(function($q) use ($now) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $now);
                     });
    }
    
    /**
     * Scope for notifications for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('target_type', 'all')
              ->orWhere(function($subQ) use ($userId) {
                  // Get user's employee data
                  $user = User::find($userId);
                  if ($user && $user->employee) {
                      $subQ->where('target_type', 'department')
                           ->whereJsonContains('target_departments', $user->employee->department);
                  }
              })
              ->orWhere(function($subQ) use ($userId) {
                  $user = User::find($userId);
                  if ($user && $user->employee) {
                      // Use employee table id (primary key) instead of employee_id
                      // Cast to int to match JSON array type
                      $subQ->where('target_type', 'specific_employees')
                           ->whereJsonContains('target_employees', (int)$user->employee->id);
                  }
              });
        });
    }
    
    /**
     * Check if user can view this notification
     */
    public function canUserView(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) return false;
        
        switch ($this->target_type) {
            case 'all':
                return true;
                
            case 'department':
                if ($this->target_departments && $user->employee) {
                    return in_array($user->employee->department, $this->target_departments);
                }
                return false;
                
            case 'specific_employees':
                if ($this->target_employees && $user->employee) {
                    // Use employee table id (primary key) instead of employee_id
                    return in_array($user->employee->id, $this->target_employees);
                }
                return false;
                
            default:
                return false;
        }
    }
}
