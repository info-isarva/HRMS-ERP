<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    protected $fillable = [
        'name', 
        'short_name', 
        'type', 
        'status', 
        'calculation_type', 
        'calculation_value', 
        'is_residual',
        'location_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_residual' => 'boolean',
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
}
