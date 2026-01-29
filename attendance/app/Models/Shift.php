<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'description'];

    public function dutyRosters()
    {
        return $this->hasMany(DutyRoster::class);
    }
}
