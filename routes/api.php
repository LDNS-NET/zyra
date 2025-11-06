<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenants\CaptivePortalController;
use App\Http\Controllers\Api\RadiusController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Captive Portal API Routes (public, tenant-initialized)
|--------------------------------------------------------------------------
*/

Route::middleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])->group(function () {
    // Tenant info for captive portal UI
    Route::get('/captive-portal/tenant', [CaptivePortalController::class, 'tenant']);

    // Hotspot package listing
    Route::get('/hotspot/packages', [CaptivePortalController::class, 'packages']);

    // Hotspot actions
    Route::post('/hotspot/login', [CaptivePortalController::class, 'login']);
    Route::post('/hotspot/voucher', [CaptivePortalController::class, 'voucher']);
    Route::post('/hotspot/pay', [CaptivePortalController::class, 'pay']);

    // Webhook callback from IntaSend
    Route::post('/hotspot/payment/callback', [CaptivePortalController::class, 'paymentCallback']);

    // Simple polling endpoint for frontend to check payment status by id or invoice
    Route::get('/hotspot/payment-status/{identifier}', [CaptivePortalController::class, 'paymentStatus']);

});


Route::post('/radius/auth', [RadiusController::class, 'auth']);

