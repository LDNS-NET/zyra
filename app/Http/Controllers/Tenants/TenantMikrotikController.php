<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenants\{
    TenantMikrotik,
    TenantOpenVPNProfile,
    TenantRouterLog,
    TenantBandwidthUsage,
    TenantActiveSession,
    TenantRouterAlert
};
use App\Services\{MikrotikService, MikrotikScriptGenerator};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantMikrotikController extends Controller
{
    /**
     * List all Mikrotiks for tenant.
     */
    public function index()
    {
        $routers = TenantMikrotik::with(['openvpnProfile', 'logs', 'bandwidthUsage', 'alerts'])
            ->orderByDesc('last_seen_at')
            ->get();

        try {
            $openvpnProfiles = TenantOpenVPNProfile::where('status', 'active')
                ->orderBy('config_path')
                ->get();
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, return empty collection
            $openvpnProfiles = collect([]);
        }

        return Inertia::render('Mikrotiks/Index', [
            'routers' => $routers,
            'openvpnProfiles' => $openvpnProfiles,
        ]);
    }

    /**
     * Show single router details.
     */
    public function show($id)
    {
        $router = TenantMikrotik::with(['openvpnProfile', 'logs', 'bandwidthUsage', 'activeSessions', 'alerts'])
            ->findOrFail($id);

        return Inertia::render('Mikrotiks/Show', compact('router'));
    }

    /**
     * Store a new Mikrotik and show onboarding script.
     */
    public function store(Request $request, MikrotikScriptGenerator $scriptGenerator)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'router_username' => 'required|string|max:255',
            'router_password' => 'required|string|min:1',
            'notes' => 'nullable|string',
        ]);

        $router = TenantMikrotik::create([
            'name' => $data['name'],
            'router_username' => $data['router_username'],
            'router_password' => $data['router_password'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'sync_token' => Str::random(40),
        ]);

        $caUrl = optional($router->openvpnProfile)->ca_cert_path
            ? route('mikrotiks.downloadCACert', $router->id)
            : null;

        // Get server IP (trusted IP) - use config or request IP
        $trustedIp = config('app.server_ip') ?? request()->server('SERVER_ADDR') ?? '207.154.204.144';
        
        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'router_password' => $router->router_password,
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $caUrl,
            'api_port' => $router->api_port ?? 8728,
            'trusted_ip' => $trustedIp,
            'radius_ip' => '207.154.204.144', // TODO: Get from tenant settings
            'radius_secret' => 'ZyraafSecret123', // TODO: Get from tenant settings
        ]);

        return Inertia::render('Mikrotiks/SetupScript', [
            'router' => $router,
            'script' => $script,
        ]);
    }

    /**
     * Update router details & recheck connection.
     */
    public function update(Request $request, $id)
    {
        $router = TenantMikrotik::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'ip_address' => 'required|ip',
            'api_port' => 'required|integer',
            'ssh_port' => 'required|integer',
            'openvpn_profile_id' => 'nullable|integer',
            'router_username' => 'required|string',
            'router_password' => 'nullable|string',
            'connection_type' => 'required|in:api,ssh,ovpn',
            'notes' => 'nullable|string',
        ]);

        if (empty($data['router_password'])) unset($data['router_password']);
        $router->update($data);

        $isOnline = $this->testRouterConnection($router);
        $router->status = $isOnline ? 'online' : 'offline';
        if ($isOnline) $router->last_seen_at = now();
        $router->save();

        $router->logs()->create([
            'action' => 'update',
            'message' => $isOnline ? 'Router is online after update.' : 'Router offline after update.',
            'status' => $isOnline ? 'success' : 'failed',
        ]);

        return redirect()->route('mikrotiks.index')->with('success', 'Router updated!');
    }

    public function destroy($id)
    {
        TenantMikrotik::findOrFail($id)->delete();
        return redirect()->route('mikrotiks.index')->with('success', 'Router deleted!');
    }

    /**
     * Check router connectivity.
     */
    public function testConnection($id)
    {
        $router = TenantMikrotik::findOrFail($id);

        $isOnline = $this->testRouterConnection($router);

        return response()->json([
            'success' => $isOnline,
            'message' => $isOnline ? 'Router is online.' : 'Router is offline.',
            'status' => $router->status,
            'last_seen_at' => $router->last_seen_at,
        ]);
    }

    /**
     * Used by the Vue SetupScript.vue component.
     */
    public function pingRouter($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        $isOnline = $this->testRouterConnection($router);

        return response()->json([
            'success' => $isOnline,
            'status' => $isOnline ? 'online' : 'offline',
            'message' => $isOnline ? 'Router is online!' : 'Router is offline!',
            'last_seen_at' => $router->last_seen_at,
        ]);
    }

    /**
     * Download setup script for router.
     */
    public function downloadSetupScript($id, MikrotikScriptGenerator $scriptGenerator)
    {
        $router = TenantMikrotik::findOrFail($id);

        $caUrl = optional($router->openvpnProfile)->ca_cert_path
            ? route('mikrotiks.downloadCACert', $router->id)
            : null;

        // Get server IP (trusted IP) - use config or request IP
        $trustedIp = config('app.server_ip') ?? request()->server('SERVER_ADDR') ?? '207.154.204.144';
        
        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'router_password' => $router->router_password,
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $caUrl,
            'api_port' => $router->api_port ?? 8728,
            'trusted_ip' => $trustedIp,
            'radius_ip' => '207.154.204.144', // TODO: Get from tenant settings
            'radius_secret' => 'ZyraafSecret123', // TODO: Get from tenant settings
        ]);

        $router->logs()->create([
            'action' => 'download_script',
            'message' => 'Setup script downloaded',
            'status' => 'success',
        ]);

        return response($script)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=setup_router_{$router->id}.rsc");
    }

    /**
     * Reprovision router - regenerate onboarding script.
     */
    public function reprovision($id, MikrotikScriptGenerator $scriptGenerator)
    {
        $router = TenantMikrotik::findOrFail($id);

        $caUrl = optional($router->openvpnProfile)->ca_cert_path
            ? route('mikrotiks.downloadCACert', $router->id)
            : null;

        // Get server IP (trusted IP) - use config or request IP
        $trustedIp = config('app.server_ip') ?? request()->server('SERVER_ADDR') ?? '207.154.204.144';
        
        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'router_password' => $router->router_password,
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $caUrl,
            'api_port' => $router->api_port ?? 8728,
            'trusted_ip' => $trustedIp,
            'radius_ip' => '207.154.204.144', // TODO: Get from tenant settings
            'radius_secret' => 'ZyraafSecret123', // TODO: Get from tenant settings
        ]);

        return Inertia::render('Mikrotiks/SetupScript', compact('router', 'script'));
    }

    /**
     * Handle router phone-home sync endpoint.
     */
    public function sync($mikrotik, Request $request)
    {
        $router = TenantMikrotik::findOrFail($mikrotik);

        $token = $request->query('token') ?? $request->input('token');
        if ($token !== $router->sync_token) {
            return response()->json(['success' => false, 'message' => 'Invalid sync token.'], 403);
        }

        // Get router IP from request (if provided) or use client IP
        $routerIp = $request->input('ip_address') ?? $request->ip();
        
        $updateData = [
            'status' => 'online',
            'last_seen_at' => now(),
        ];
        
        // Update IP address if provided and different
        if ($routerIp && $routerIp !== $router->ip_address) {
            $updateData['ip_address'] = $routerIp;
        }

        $router->update($updateData);

        $router->logs()->create([
            'action' => 'sync',
            'message' => 'Router phone-home sync received. IP: ' . ($routerIp ?? 'not provided'),
            'status' => 'success',
            'response_data' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sync received. Router marked online.',
            'status' => 'online',
            'ip_address' => $router->ip_address,
            'last_seen_at' => $router->last_seen_at,
        ]);
    }

    /**
     * Shared logic to test router connectivity.
     */
    private function testRouterConnection(TenantMikrotik $router): bool
    {
        try {
            if (!$router->ip_address) {
                return false;
            }

            $service = MikrotikService::forMikrotik($router)
                ->setConnection(
                    $router->ip_address,
                    $router->router_username,
                    $router->router_password,
                    $router->api_port ?? 8728,
                    $router->use_ssl ?? false
                );

            $resources = $service->testConnection();
            $isOnline = $resources !== false;

            $router->status = $isOnline ? 'online' : 'offline';
            if ($isOnline) {
                $router->last_seen_at = now();
            }
            $router->save();

            $router->logs()->create([
                'action' => 'ping',
                'message' => $isOnline ? 'Router responded successfully.' : 'Router did not respond.',
                'status' => $isOnline ? 'success' : 'failed',
            ]);

            return $isOnline;
        } catch (\Throwable $e) {
            Log::error("Router test failed: {$e->getMessage()}");
            $router->logs()->create([
                'action' => 'ping',
                'message' => 'Error during router connection test: ' . $e->getMessage(),
                'status' => 'failed',
            ]);
            return false;
        }
    }

    /**
     * Validate router connection before saving.
     */
    public function validateRouter(Request $request)
    {
        $data = $request->validate([
            'ip_address' => 'required|ip',
            'router_username' => 'required|string',
            'router_password' => 'required|string',
            'api_port' => 'nullable|integer|min:1|max:65535',
        ]);

        try {
            $service = new MikrotikService();
            $service->setConnection(
                $data['ip_address'],
                $data['router_username'],
                $data['router_password'],
                $data['api_port'] ?? 8728,
                false
            );

            $resources = $service->testConnection();
            $isValid = $resources !== false;

            return response()->json([
                'success' => $isValid,
                'message' => $isValid ? 'Connection successful.' : 'Connection failed.',
                'resources' => $isValid ? $resources : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download RADIUS setup script.
     */
    public function downloadRadiusScript($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        
        // Load the RADIUS script template
        $templatePath = resource_path('scripts/mikrotik/setup-radius.rsc');
        $script = file_exists($templatePath) ? file_get_contents($templatePath) : '';

        if (!$script) {
            return redirect()->route('mikrotiks.index')
                ->with('error', 'RADIUS script template not found.');
        }

        $router->logs()->create([
            'action' => 'download_radius_script',
            'message' => 'RADIUS setup script downloaded',
            'status' => 'success',
        ]);

        return response($script)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=radius_setup_{$router->id}.rsc");
    }

    /**
     * Get remote management links for router.
     */
    public function remoteManagement($id)
    {
        $router = TenantMikrotik::findOrFail($id);

        if (!$router->ip_address) {
            return response()->json([
                'success' => false,
                'message' => 'Router IP address not configured.',
            ], 400);
        }

        $sshPort = $router->ssh_port ?? 22;
        $apiPort = $router->api_port ?? 8728;
        
        $links = [
            'winbox' => "winbox://{$router->ip_address}",
            'ssh' => "ssh://{$router->router_username}@{$router->ip_address}:{$sshPort}",
            'api' => "http://{$router->ip_address}:{$apiPort}",
        ];

        return response()->json($links);
    }

    /**
     * Download OpenVPN CA certificate.
     */
    public function downloadCACert($id)
    {
        $router = TenantMikrotik::findOrFail($id);
        
        if (!$router->openvpnProfile || !$router->openvpnProfile->ca_cert_path) {
            return response()->json([
                'success' => false,
                'message' => 'OpenVPN CA certificate not configured.',
            ], 404);
        }

        $certPath = storage_path('app/' . $router->openvpnProfile->ca_cert_path);
        
        if (!file_exists($certPath)) {
            return response()->json([
                'success' => false,
                'message' => 'CA certificate file not found.',
            ], 404);
        }

        return response()->download($certPath, 'ca.crt', [
            'Content-Type' => 'application/x-x509-ca-cert',
        ]);
    }
}
