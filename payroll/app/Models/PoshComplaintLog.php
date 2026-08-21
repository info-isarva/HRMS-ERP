<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoshComplaintLog extends Model
{
    protected $table = 'posh_complaint_logs';

    protected $fillable = [
        'complaint_id',
        'action_by_user_id',
        'action_type',
        'notes',
        'minutes_of_meeting',
        'attachment_path',
        'original_filename',
    ];

    public function complaint()
    {
        return $this->belongsTo(PoshComplaint::class, 'complaint_id');
    }

    public function actionByUser()
    {
        return $this->belongsTo(User::class, 'action_by_user_id');
    }
}
