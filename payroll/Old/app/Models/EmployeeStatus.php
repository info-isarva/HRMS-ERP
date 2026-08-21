<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_name',
        'short_name',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope a query to only include active statuses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
