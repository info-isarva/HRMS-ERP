<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_id',
        'previous_joining_date',
        'previous_exit_date',
        'exit_type',
    ];

    protected $casts = [
        'previous_joining_date' => 'date',
        'previous_exit_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id');
    }
}
