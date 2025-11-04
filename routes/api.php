<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenants\CaptivePortalController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\TenantRadiusController;


/*
|--------------------------------------------------------------------------
| Captive Portal API Routes (public, tenant-initialized)
|--------------------------------------------------------------------------
|
| These routes are intentionally public (no auth) so captive portal devices
| and networks can interact with them. Tenancy middleware initializes the
| tenant context based on domain so tenant-scoped models work correctly.
|
*/

Route::middleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
    ->prefix('radius')
    ->group(function () {
        Route::post('/auth', [TenantRadiusController::class, 'auth']);
        Route::post('/acct', [TenantRadiusController::class, 'acct']);
        Route::post('/disconnect', [TenantRadiusController::class, 'disconnect']);
    });