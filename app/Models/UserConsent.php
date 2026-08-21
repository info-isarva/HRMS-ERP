<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserConsent extends Model
{
    use HasFactory;

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
