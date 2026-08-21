<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'position_types';
    
    protected $fillable = [
        'position',
        'short_name',
        'description',
        'location_id',
        'status',
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
     * Get employees with this position/designation
     */
    public function employees()
    {
        return $this->hasMany(EmployeeBasicDetail::class, 'designation', 'id');
    }
}
