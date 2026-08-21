<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_payroll_id',
        'user_id',
        'month',
        'year',
        'status',
        'notes',
        'reviewed_by',
    ];

    /**
     * Get the user that owns the attendance review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who reviewed this request.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the employee details via employee_payroll_id.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }
}
