<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshPreventionEvent extends Model
{
    protected $fillable = ['organization_id', 'event_type', 'title', 'held_on', 'attendee_count', 'notes', 'recorded_by'];

    protected function casts(): array
    {
        return ['held_on' => 'date'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
