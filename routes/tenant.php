<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\TenantSMSController;
use App\Http\Controllers\Tenants\TenantPaymentController;

Route::middleware([
    'web',
    'auth',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return inertia('Dashboard', [
            'tenant' => tenant(),
        ]);
    })->name('dashboard');

    // Tenant-specific modules
    Route::resource('sms', TenantSMSController::class);

    // Optional: bulk actions
    Route::delete('sms/bulk-delete', [TenantSMSController::class, 'bulkDestroy'])->name('sms.bulk-destroy');
    Route::delete('payments/bulk-delete', [TenantPaymentController::class, 'bulkDestroy'])->name('payments.bulk-destroy');
});
