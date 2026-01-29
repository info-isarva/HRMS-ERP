<?php

namespace App\Providers;

use App\Models\LeaveApplication;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Models\User;
use App\Policies\LeaveApplicationPolicy;
use App\Policies\PublicHolidayPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        LeaveApplication::class => LeaveApplicationPolicy::class,
        PublicHoliday::class => PublicHolidayPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for user roles
        Gate::define('manage-employees', function (User $user) {
            return in_array($user->role, ['admin', 'super_admin']);
        });
    }
}
