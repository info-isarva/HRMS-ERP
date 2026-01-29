<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use SoftDeletes;

    protected $table = 'employee_documents';

    protected $fillable = [
        'emp_id',
        'document_id',
        'uploaded_document',
        'name',
        'created_by',
        'updated_by',
    ];
    
    public function employeeDocument()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id', 'id');
    }
}
