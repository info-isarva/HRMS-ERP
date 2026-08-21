<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoshIccMember extends Model
{
    protected $table = 'posh_icc_members';

    protected $fillable = [
        'employee_id',
        'icc_role',
        'contact_number',
        'email',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id');
    }
}
