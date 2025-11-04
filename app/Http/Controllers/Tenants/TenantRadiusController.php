<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\NetworkUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TenantRadiusController extends Controller
{
    // Authenticate users (PAP, CHAP handled via REST)
    public function auth(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $user = NetworkUser::where('username', $username)->first();

        if (!$user) {
            Log::info("RADIUS: user not found [$username]");
            return response('Access-Reject', 200);
        }

        if ($user->password !== $password) {
            Log::info("RADIUS: invalid password [$username]");
            return response('Access-Reject', 200);
        }

        if ($user->expires_at && Carbon::now()->greaterThan($user->expires_at)) {
            Log::info("RADIUS: expired account [$username]");
            return response('Access-Reject', 200);
        }

        // If everything is valid
        Log::info("RADIUS: access accepted [$username]");
        return response('Access-Accept', 200)->header('Content-Type', 'text/plain');
    }

    // Accounting (Start / Stop / Update)
    public function acct(Request $request)
    {
        Log::info('RADIUS Accounting', $request->all());
        return response('OK', 200);
    }

    // Disconnect (CoA)
    public function disconnect(Request $request)
    {
        Log::info('RADIUS Disconnect', $request->all());
        return response('OK', 200);
    }
}

