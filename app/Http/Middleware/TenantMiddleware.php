<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $subdomain = $request->route('subdomain');

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        // Share tenant globally
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
