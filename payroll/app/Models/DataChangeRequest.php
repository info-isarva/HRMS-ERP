<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_email',
        'request_type',
        'details',
        'status',
        'resolved_at',
        'resolved_by',
        'source_system'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'user_email', 'email');
    }
}
