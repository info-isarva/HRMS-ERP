<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialYearSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_month',
        'auto_close_enabled',
        'auto_close_days_after',
        'auto_create_next',
        'create_next_days_before',
        'notification_settings',
        'closing_policy',
    ];

    protected $casts = [
        'auto_close_enabled' => 'boolean',
        'auto_create_next' => 'boolean',
        'notification_settings' => 'array',
    ];

    /**
     * Get the singleton settings instance
     */
    public static function getSettings()
    {
        return static::first() ?? static::create([
            'start_month' => 4, // April
            'auto_close_enabled' => true,
            'auto_close_days_after' => 30,
            'auto_create_next' => true,
            'create_next_days_before' => 30,
        ]);
    }

    /**
     * Get the financial year start month name
     */
    public function getStartMonthNameAttribute()
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        return $months[$this->start_month] ?? 'April';
    }

    /**
     * Get the financial year end month (previous month of start month)
     */
    public function getEndMonth()
    {
        return $this->start_month == 1 ? 12 : $this->start_month - 1;
    }

    /**
     * Get the financial year end month name
     */
    public function getEndMonthNameAttribute()
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        return $months[$this->getEndMonth()] ?? 'March';
    }

    /**
     * Get notification settings with defaults
     */
    public function getNotificationSettings()
    {
        return array_merge([
            'notify_on_close' => true,
            'notify_on_create' => true,
            'notify_users' => ['admin'],
            'email_template' => 'default',
        ], $this->notification_settings ?? []);
    }
}
