<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshEmployerDuty extends Model
{
    protected $fillable = ['organization_id', 'duty_key', 'duty_text', 'is_done', 'done_on', 'notes'];

    protected function casts(): array
    {
        return ['is_done' => 'boolean', 'done_on' => 'date'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
