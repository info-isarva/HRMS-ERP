<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshPolicyAcknowledgement extends Model
{
    protected $fillable = [
        'posh_policy_id',
        'user_id',
        'acknowledged_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(PoshPolicy::class, 'posh_policy_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
