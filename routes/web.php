<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Normal user controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\DashboardController;

// SuperAdmin controllers
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SuperAdminTenantController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\SuperAdminPaymentController;
use App\Http\Controllers\SuperAdmin\SuperAdminSmsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated + Subscription Checked Routes (Tenants)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'check.subscription'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // other tenant/users routes
});

/*
|--------------------------------------------------------------------------
| Payment Success Callback
|--------------------------------------------------------------------------
*/
Route::get('/payment/success', function () {
    $user = auth()->user();
    if ($user) {
        $user->update([
            'subscription_expires_at' => now()->addDays(30),
            'is_suspended' => false,
        ]);
    }
    return redirect()->route('dashboard');
})->name('payment.success');

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| SuperAdmin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

        // other superadmin routes
    });

require __DIR__.'/auth.php';
