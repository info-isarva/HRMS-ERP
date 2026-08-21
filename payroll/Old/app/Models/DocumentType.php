<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'short_name',
        'description',
        'location_id',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'location_id' => 'array',
    ];

    public function getLocationNameAttribute()
    {
        if (empty($this->location_id)) {
            return 'N/A';
        }
        
        if (in_array('0', (array)$this->location_id)) {
            return 'All';
        }

        return Location::whereIn('id', (array)$this->location_id)->pluck('name')->implode(', ');
    }

    /**
     * Scope a query to only include active document types.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
