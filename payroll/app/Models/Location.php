<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'short_name', 
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope to get only active locations
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
