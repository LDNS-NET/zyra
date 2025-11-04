<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\NetworkUser;
use Illuminate\Support\Facades\Log;

class TenantRadiusController extends Controller
{
    /**
     * Handle authentication requests from FreeRADIUS.
     */
    public function auth(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        Log::info('RADIUS AUTH request', [
            'tenant' => tenant('id'),
            'username' => $username,
        ]);

        $user = NetworkUser::where('username', $username)->first();

        if (! $user) {
            return response('Access-Reject', 200);
        }

        if ($user->password !== $password) {
            return response('Access-Reject', 200);
        }

        // Optional expiry check
        if ($user->expires_at && now()->greaterThan($user->expires_at)) {
            return response('Access-Reject', 200);
        }

        // Optional account active check
        if ($user->status === 'disabled') {
            return response('Access-Reject', 200);
        }

        // Accept
        return response('Access-Accept', 200);
    }

    /**
     * Handle accounting updates (session start/stop).
     */
    public function acct(Request $request)
    {
        Log::info('RADIUS ACCT update', [
            'tenant' => tenant('id'),
            'data' => $request->all(),
        ]);

        // You can handle accounting (start, stop, interim updates)
        // Example: update session time, data used, etc.
        return response('OK', 200);
    }

    /**
     * Handle disconnect requests.
     */
    public function disconnect(Request $request)
    {
        Log::info('RADIUS DISCONNECT request', [
            'tenant' => tenant('id'),
            'data' => $request->all(),
        ]);

        // You can implement logic to terminate user session on MikroTik
        return response('OK', 200);
    }
}
