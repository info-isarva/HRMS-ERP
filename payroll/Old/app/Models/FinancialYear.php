<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinancialYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_closed',
        'closed_at',
        'closing_summary',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
        'closing_summary' => 'array',
    ];

    /**
     * Get the current active financial year
     */
    public static function current()
    {
        return static::where('is_current', true)->first();
    }

    /**
     * Get all open financial years
     */
    public static function open()
    {
        return static::where('is_closed', false);
    }

    /**
     * Get all closed financial years
     */
    public static function closed()
    {
        return static::where('is_closed', true);
    }

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

    /**
     * Check if this financial year is active (current)
     */
    public function isCurrent()
    {
        return $this->is_current;
    }

    /**
     * Check if this financial year is closed
     */
    public function isClosed()
    {
        return $this->is_closed;
    }

    /**
     * Check if this financial year can be closed
     */
    public function canBeClosed()
    {
        return !$this->is_closed && Carbon::now()->gt($this->end_date);
    }

    /**
     * Get the financial year duration in days
     */
    public function getDurationInDays()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get the financial year progress percentage
     */
    public function getProgressPercentage()
    {
        $now = Carbon::now();
        
        if ($now->lt($this->start_date)) {
            return 0;
        }
        
        if ($now->gt($this->end_date)) {
            return 100;
        }
        
        $totalDays = $this->getDurationInDays();
        $elapsedDays = $this->start_date->diffInDays($now) + 1;
        
        return round(($elapsedDays / $totalDays) * 100, 2);
    }

    /**
     * Get remaining days in financial year
     */
    public function getRemainingDays()
    {
        $now = Carbon::now();
        
        if ($now->gt($this->end_date)) {
            return 0;
        }
        
        if ($now->lt($this->start_date)) {
            return $this->getDurationInDays();
        }
        
        return $now->diffInDays($this->end_date);
    }

    /**
     * Reports relationship
     */
    public function reports()
    {
        return $this->hasMany(FinancialYearReport::class);
    }

    /**
     * Get quarterly periods for this financial year
     */
    public function getQuarters()
    {
        $quarters = [];
        $start = $this->start_date->copy();
        
        for ($i = 1; $i <= 4; $i++) {
            $quarterStart = $start->copy();
            $quarterEnd = $start->copy()->addMonths(3)->subDay();
            
            // Ensure quarter end doesn't exceed FY end
            if ($quarterEnd->gt($this->end_date)) {
                $quarterEnd = $this->end_date->copy();
            }
            
            $quarters[] = [
                'number' => $i,
                'name' => "Q{$i}",
                'start_date' => $quarterStart,
                'end_date' => $quarterEnd,
                'is_current' => Carbon::now()->between($quarterStart, $quarterEnd),
            ];
            
            $start->addMonths(3);
            
            // Break if we've reached the end of the financial year
            if ($start->gt($this->end_date)) {
                break;
            }
        }
        
        return $quarters;
    }

    /**
     * Scope for current financial year
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope for open financial years
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope for closed financial years
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }
}
