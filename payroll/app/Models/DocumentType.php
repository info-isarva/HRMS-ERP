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
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope a query to only include active document types.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
