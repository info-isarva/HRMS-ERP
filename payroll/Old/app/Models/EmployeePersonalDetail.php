<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePersonalDetail extends Model
{
    use SoftDeletes;

    protected $table = 'employee_personal_details';

    protected $fillable = [
        'emp_id',
        'address',
        'temporary_address',
        'father_name',
        'mother_name',
        'blood_group',
        'emergency_contact_name',
        'emergency_contact_number',
        'aadhaar_number',
        'pan_number',
        'pf_account_number',
        'esic_number',
        'uploaded_documents',
        'created_by',
        'updated_by',
    ];
    public function basicDetail()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id', 'id');
    }
}

