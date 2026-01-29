<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeOtDetail extends Model
{
    protected $table = 'employee_ot_details';

    protected $fillable = [
        'emp_id',
        'payout_month',
        'payout_year',
        'ot_hours',
        'ot_rate',
        'total_amount',
        'created_by',
        'updated_by',
    ];
    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id');
    }
}
