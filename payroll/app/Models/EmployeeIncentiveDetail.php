<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIncentiveDetail extends Model
{
    protected $fillable = [
        'emp_id', 'payout_month', 'payout_year', 
        'incentive_days', 'incentive_rate', 'total_amount',
        'created_by', 'updated_by'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id');
    }
}
