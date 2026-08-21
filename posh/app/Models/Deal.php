<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime',
    ];
    use HasFactory;

    protected $table = 'deals';

    protected $fillable = [
        'title',
        'description',
        'amount',
        'label',
        'lead_source',
        'category',
        'organization_id',
        'customer_id',
        'people_id',
        'value',
        'stage',
        'reason_for_loss',
        'probability', // Added probability field
        'status',
        'close_date',
        'assigned_id',
        'user_owner_id',
        'created_by',
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'converted_lead_id',
    ];

    public $timestamps = false;

    /**
     * Get the owner (user) of the lead.
     */
    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_owner_id');
    }

    /**
     * Get the customer associated with the lead.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    /**
     * Get the organization associated with the lead.
     */
    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class, 'organization_id');
    }

    /**
     * Get the person (contact) associated with the lead.
     */
    public function person()
    {
        return $this->belongsTo(\App\Models\Person::class, 'people_id');
    }

     /**
     * Get the deal source model associated with the lead.
     */
    public function dealSource()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage');
    }

    /**
     * Get the category associated with the deal.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category', 'id');
    }
}
