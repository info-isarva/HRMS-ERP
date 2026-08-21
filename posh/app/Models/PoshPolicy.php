<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoshPolicy extends Model
{
    protected $fillable = [
        'organization_id',
        'version',
        'title',
        'content',
        'file_path',
        'is_active',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(PoshPolicyAcknowledgement::class);
    }
}
