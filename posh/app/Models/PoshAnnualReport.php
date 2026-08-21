<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshAnnualReport extends Model
{
    protected $fillable = ['organization_id', 'report_year', 'report_data', 'generated_at', 'submitted_at', 'generated_by'];

    protected function casts(): array
    {
        return [
            'report_data' => 'array',
            'generated_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
