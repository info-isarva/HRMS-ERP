<?php

namespace App\Policies;

use App\Models\PublicHoliday;
use App\Models\User;

class PublicHolidayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view holidays
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PublicHoliday $publicHoliday): bool
    {
        return true; // All authenticated users can view individual holidays
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PublicHoliday $publicHoliday): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PublicHoliday $publicHoliday): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PublicHoliday $publicHoliday): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PublicHoliday $publicHoliday): bool
    {
        return $user->isSuperAdmin();
    }
}
