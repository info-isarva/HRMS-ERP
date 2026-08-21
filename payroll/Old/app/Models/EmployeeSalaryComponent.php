<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalaryComponent extends Model
{
    use SoftDeletes;

    protected $table = 'employee_salary_components';

    protected $fillable = [
        'emp_id',
        'salary_component_id',
        'value',
        'created_by',
        'updated_by',
    ];
    public function basicDetail()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id', 'id');
    }

    public function salaryComponents()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
