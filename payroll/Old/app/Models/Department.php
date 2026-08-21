<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'department',
        'short_name', 
        'description',
        'location_id',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'location_id' => 'array',
    ];

    public function getLocationNameAttribute()
    {
        if (empty($this->location_id)) {
            return 'N/A';
        }
        
        if (in_array('0', (array)$this->location_id)) {
            return 'All';
        }

        return Location::whereIn('id', (array)$this->location_id)->pluck('name')->implode(', ');
    }

    /**
     * Scope to get only active departments
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
