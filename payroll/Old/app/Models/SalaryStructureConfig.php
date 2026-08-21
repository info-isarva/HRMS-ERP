<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructureConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_component_id',
        'calculation_type',
        'value',
        'percentage_of',
        'status',
    ];

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
