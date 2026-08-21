<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'organization_id',
        'hub_user_id',
        'name',
        'email',
        'password',
        'employee_code',
        'department',
        'designation',
        'posh_role',
        'user_source',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function policyAcknowledgements(): HasMany
    {
        return $this->hasMany(PoshPolicyAcknowledgement::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->posh_role, config('posh.admin_roles'), true);
    }

    public function canManageIc(): bool
    {
        return in_array($this->posh_role, config('posh.admin_roles'), true);
    }

    public function canManagePolicy(): bool
    {
        return in_array($this->posh_role, config('posh.admin_roles'), true);
    }

    public function hasIcAccess(): bool
    {
        return in_array($this->posh_role, config('posh.ic_roles_access'), true);
    }

    public function filedComplaints()
    {
        return $this->hasMany(PoshComplaint::class, 'filed_by_user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
