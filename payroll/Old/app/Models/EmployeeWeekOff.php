<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWeekOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'week_off_days',
        'week_off_pattern',
        'working_days_per_week'
    ];

    protected $casts = [
        'week_off_days' => 'array'
    ];

    /**
     * Get the employee that owns the week off configuration
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id');
    }

    /**
     * Get day name from day number
     */
    public static function getDayName($dayNumber)
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday', 
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];
        
        return $days[$dayNumber] ?? 'Unknown';
    }

    /**
     * Get formatted week off pattern
     */
    public function getFormattedPatternAttribute()
    {
        if (empty($this->week_off_days)) {
            return 'No week offs';
        }

        $dayNames = array_map(function($day) {
            return self::getDayName($day);
        }, $this->week_off_days);

        return implode(', ', $dayNames);
    }

    /**
     * Calculate working days per week
     */
    public function calculateWorkingDays()
    {
        $totalDays = 7;
        $weekOffDays = count($this->week_off_days ?? []);
        return $totalDays - $weekOffDays;
    }

    /**
     * Check if a specific day is a week off
     */
    public function isDayOff($dayNumber)
    {
        return in_array($dayNumber, $this->week_off_days ?? []);
    }

    /**
     * Get week off configuration for API
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'week_off_days' => $this->week_off_days,
            'week_off_pattern' => $this->formatted_pattern,
            'working_days_per_week' => $this->working_days_per_week,
            'day_names' => array_map(function($day) {
                return [
                    'day_number' => $day,
                    'day_name' => self::getDayName($day)
                ];
            }, $this->week_off_days ?? []),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }
}