<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'email',
        'phone',
        'mobile',
        'job_title',
        'lead_source',
        'user_owner_id',
        'organization_id',
        'customer_id',
        'linkedin',
        'address',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    // Add owner relationship (for user_owner_id field)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_owner_id');
    }

        // Add organization relationship
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    // Relationship with leads
    public function leads()
    {
        return $this->hasMany(Lead::class, 'people_id');
    }

    // Relationship with deals
    public function deals()
    {
        return $this->hasMany(Deal::class, 'people_id');
    }
}
