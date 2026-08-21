<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConsent extends Model
{
    protected $fillable = [
        'user_id',
        'policy_type',
        'is_accepted',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
        'accepted_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
