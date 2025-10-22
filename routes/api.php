<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenants\CaptivePortalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public endpoints consumed by captive portal UI. These live under /api/*
| so they are not subject to web CSRF middleware.
|
*/

Route::get('/captive-portal/tenant', [CaptivePortalController::class, 'tenant']);
Route::get('/hotspot/packages', [CaptivePortalController::class, 'packages']);
Route::post('/hotspot/login', [CaptivePortalController::class, 'login']);
Route::post('/hotspot/voucher', [CaptivePortalController::class, 'voucher']);
Route::post('/hotspot/pay', [CaptivePortalController::class, 'pay']);
// IntaSend webhook (use full absolute URL when initiating STK)
Route::post('/hotspot/payment/callback', [CaptivePortalController::class, 'paymentCallback']);
