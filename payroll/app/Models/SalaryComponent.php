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
        'is_residual'
    ];
}
