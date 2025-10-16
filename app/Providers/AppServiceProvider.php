<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\CheckSubscription;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SuperAdminMiddleware;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Tenants\TenantLeads;
use App\Models\Tenants\NetworkUser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register the CheckSubscription middleware globally
       Route::aliasMiddleware('check.subscription', CheckSubscription::class);
       Route::aliasMiddleware('superadmin', SuperAdminMiddleware::class);

       Relation::enforceMorphMap([
        'lead' => TenantLeads::class,
        'user' => NetworkUser::class,
    ]);
        
    }
}
