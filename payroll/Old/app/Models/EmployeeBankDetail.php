<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBankDetail extends Model
{
    use SoftDeletes;

    protected $table = 'employee_bank_details';

    protected $fillable = [
        'emp_id',
        'type_of_payment',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch',
        'transaction_type',
        'created_by',
        'updated_by',
    ];
    
    public function basicDetail()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id', 'id');
    }
}
