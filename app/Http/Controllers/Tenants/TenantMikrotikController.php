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

        return Inertia::render('Mikrotiks/Index', [
            'routers' => $routers,
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

        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'password' => $router->router_password,
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $caUrl,
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

        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'password' => $router->router_password,
            'router_id' => $router->id,
            'ca_url' => $caUrl,
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

        $script = $scriptGenerator->generate([
            'name' => $router->name,
            'username' => $router->router_username,
            'password' => $router->router_password,
            'router_id' => $router->id,
            'sync_token' => $router->sync_token,
            'ca_url' => $caUrl,
        ]);

        return Inertia::render('Mikrotiks/SetupScript', compact('router', 'script'));
    }

    /**
     * Handle router phone-home sync endpoint.
     */
    public function sync($router_id, Request $request)
    {
        $router = TenantMikrotik::find($router_id);

        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Router not found.'], 404);
        }

        $token = $request->query('token');
        if ($token !== $router->sync_token) {
            return response()->json(['success' => false, 'message' => 'Invalid sync token.'], 403);
        }

        $router->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        $router->logs()->create([
            'action' => 'sync',
            'message' => 'Router phone-home sync received.',
            'status' => 'success',
            'response_data' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sync received. Router marked online.',
            'status' => 'online',
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

            $service = new MikrotikService(
                $router->ip_address,
                $router->router_username,
                $router->router_password,
                $router->api_port
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
}
