<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    protected $table = 'permissions';

    protected $fillable = [
        'parent_id',       
        'name',
        'guard_name',
        'description',
        'crm_permission',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function parentPermission()
    {
        return $this->belongsTo(\App\Models\ParentPermission::class, 'parent_id');
    }
}
