<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshComplaintEvidence extends Model
{
    protected $table = 'posh_complaint_evidence';

    protected $fillable = [
        'posh_complaint_id',
        'uploaded_by_user_id',
        'storage_path',
        'original_filename',
        'mime_type',
        'file_size',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(PoshComplaint::class, 'posh_complaint_id');
    }
}
