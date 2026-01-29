<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionType extends Model
{
    protected $fillable = [
        'position',
        'short_name', 
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope to get only active position types
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }
}
