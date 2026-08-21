<?php

namespace App\Providers;

use App\Services\TenantContext;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\ServiceProvider;
use App\Models\CompanySettings;
use App\Models\User;
use App\Models\EmployeeBasicDetail;
use App\Observers\UserObserver;
use App\Observers\EmployeeBasicDetailObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(TenantDatabaseManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $companySettings = CompanySettings::first(); // or where('id', 1)->first();
        view()->share('companySettings', $companySettings);
        
        // Register observers
        User::observe(UserObserver::class);
        EmployeeBasicDetail::observe(EmployeeBasicDetailObserver::class);
    }
}
