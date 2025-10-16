<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Renter;
use App\Models\Payment;
use App\Models\SmsLog;
use App\Models\User;
use App\Models\EmailLog;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        return Inertia::render('Dashboard', [
            'subscription_expires_at' => $user?->subscription_expires_at, // Pass to Vue
        ]);
    }

    // Optional JSON endpoint for live updates
    

    
}