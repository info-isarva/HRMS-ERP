<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'logo', 'address', 'city', 'pincode', 'phone',
        'mobile', 'email', 'website', 'favicon',
        'fy_start_month', 'fy_start_day', 'fy_end_month', 'fy_end_day', 'currency_code', 'currency_symbol', 'currency_position', 'country'
    ];
}
