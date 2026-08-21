<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StaticNotificationUser extends Model
{
    use Notifiable;
    
    protected $fillable = ['name', 'email'];
    
    public $timestamps = false;
    
    // Override the route key name to use email
    public function getRouteKeyName()
    {
        return 'email';
    }
    
    // Create static instance for HR
    public static function hr()
    {
        return new self([
            'name' => 'HR Team',
            'email' => 'saikiran@idaksh.in'
        ]);
    }
    
    // Create static instance for Reporting Manager
    public static function reportingManager()
    {
        return new self([
            'name' => 'Reporting Manager',
            'email' => 'akash@idaksh.in'
        ]);
    }
}