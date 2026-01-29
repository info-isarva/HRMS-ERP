<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $table = 'email_settings';

    protected $fillable = [
        'emails_enabled',
        'updated_by'
    ];

    protected $casts = [
        'emails_enabled' => 'boolean',
    ];
}
