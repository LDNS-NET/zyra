<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenants\CaptivePortalController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public endpoints consumed by captive portal UI. These live under /api/*
| and must initialize tenancy by domain so tenant models (Packages, etc.)
| use the tenant database connection.
|
*/

Route::middleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])->group(function () {
    Route::get('/captive-portal/tenant', [CaptivePortalController::class, 'tenant']);
    Route::get('/hotspot/packages', [CaptivePortalController::class, 'packages']);
    Route::post('/hotspot/login', [CaptivePortalController::class, 'login']);
    Route::post('/hotspot/voucher', [CaptivePortalController::class, 'voucher']);
    Route::post('/hotspot/pay', [CaptivePortalController::class, 'pay']);
    // IntaSend webhook (use full absolute URL when initiating STK)
    Route::post('/hotspot/payment/callback', [CaptivePortalController::class, 'paymentCallback']);
});
