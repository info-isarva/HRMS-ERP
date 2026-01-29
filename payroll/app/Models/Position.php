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
        'status',
    ];

    /**
     * Get employees with this position/designation
     */
    public function employees()
    {
        return $this->hasMany(EmployeeBasicDetail::class, 'designation', 'id');
    }
}
