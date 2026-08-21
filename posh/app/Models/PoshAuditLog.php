<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshAuditLog extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'action',
        'case_number',
        'details',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
