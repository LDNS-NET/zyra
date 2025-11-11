<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenants\CaptivePortalController;
use App\Http\Controllers\Tenants\WireGuardController;
use App\Http\Controllers\Api\RadiusController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Captive Portal API Routes (public, tenant-initialized)
|--------------------------------------------------------------------------
*/

Route::middleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])->group(function () {
    // Tenant info

});


Route::post('/radius/auth', [RadiusController::class, 'auth']);

Route::post('/mikrotik/register', [WireGuardController::class, 'registerPeer']);

