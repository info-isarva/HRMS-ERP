<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeStatutoryComponent extends Model
{
    use SoftDeletes;

    protected $table = 'employee_statutory_components';

    protected $fillable = [
        'emp_id',
        'statutory_component_id',
        'value',
        'epf_option',
        'full_amount_deduct_from_ctc',
        'created_by',
        'updated_by',
    ];
    public function basicDetail()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id', 'id');
    }

    public function statutoryComponents()
    {
        return $this->belongsTo(StatutoryComponent::class, 'statutory_component_id');
    }

    public function statutoryComponent()
    {
        return $this->belongsTo(StatutoryComponent::class, 'statutory_component_id');
    }
}

