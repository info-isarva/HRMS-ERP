<?php

namespace App\Providers;

use App\Services\TenantContext;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\PublicHoliday;
use App\Policies\PublicHolidayPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(TenantDatabaseManager::class);

        $helperPath = app_path('Helpers/helpers.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        // Register policies
        Gate::policy(PublicHoliday::class, PublicHolidayPolicy::class);

        // View composer for header birthdays
        View::composer('layouts.header', function ($view) {
            $today = now()->format('m-d');
            $birthdayEmployees = \App\Models\Employee::all()->filter(function ($employee) use ($today) {
                $additionalData = $employee->additional_data;
                // If it's a string, decode it
                if (is_string($additionalData)) {
                    $additionalData = json_decode($additionalData, true);
                }
                if (is_array($additionalData) && isset($additionalData['date_of_birth'])) {
                    $dob = $additionalData['date_of_birth'];
                    $monthDay = date('m-d', strtotime($dob));
                    return $monthDay === $today;
                }
                return false;
            });
            $view->with('birthdayEmployees', $birthdayEmployees);
        });
    }
}
