<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshComplaintLog extends Model
{
    protected $fillable = [
        'posh_complaint_id',
        'user_id',
        'action_type',
        'old_status',
        'new_status',
        'notes',
        'attachment_path',
        'original_filename',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(PoshComplaint::class, 'posh_complaint_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
