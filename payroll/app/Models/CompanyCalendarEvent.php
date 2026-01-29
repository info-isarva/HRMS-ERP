<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'event_class',
        'recurrence_type',
        'recurrence_end_date',
        'created_by',
    ];

    /**
     * Get the user that created the event.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
