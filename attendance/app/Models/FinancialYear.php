<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinancialYear extends Model
{
    use HasFactory;

    /**
     * Get financial year by date
     */
    public static function getByDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        
        return static::where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->first();
    }

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get the currently active financial year.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the financial year is open.
     */
    public function isOpen()
    {
        return $this->status === 'open';
    }

    /**
     * Accessor for is_closed compatibility.
     */
    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'close' || $this->status === 'closed';
    }

    /**
     * Accessor for is_current compatibility.
     */
    public function getIsCurrentAttribute(): bool
    {
        return (bool) $this->is_active;
    }
}
