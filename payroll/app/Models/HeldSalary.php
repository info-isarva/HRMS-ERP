<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldSalary extends Model
{
    use HasFactory;

    protected $table = 'held_salaries';

    protected $fillable = [
        'employee_id',
        'hold_type',
        'payout_month',
        'payout_year',
        'remarks',
        'status',
        'released_at',
        'released_by',
        'created_by',
        'updated_by'
    ];

    public function employee()
    {
        // Adjust model name based on actual employee model
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id', 'id');
    }

    public function releaser()
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
