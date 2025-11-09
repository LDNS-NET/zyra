<?php

namespace App\Services;

class MikrotikScriptGenerator
{
    /**
     * Generate a full, system-ready onboarding script for Mikrotik routers.
     *
     * @param array $options
     *   - name: Desired router name
     *   - username: API/system username
     *   - router_password: API/system password
     *   - router_id: Router database ID
     *   - tenant_id: Tenant ID (optional)
     *   - ca_url: OpenVPN CA certificate URL (optional)
     *   - radius_ip: RADIUS server IP (optional)
     *   - radius_secret: RADIUS shared secret (optional)
     *   - sync_token: Router sync token (optional)
     *   - trusted_ip: Trusted IP for API/SSH/Winbox access (optional)
     * @return string
     */
    public function generate(array $options): string
    {
        $name = $options['name'] ?? 'ISP-Managed';
        $username = $options['username'] ?? 'apiuser';
        $router_password = $options['router_password'] ?? 'apipassword';
        $router_id = $options['router_id'] ?? 'ROUTER_ID';
        $tenant_id = $options['tenant_id'] ?? 'TENANT_ID';
        $ca_url = $options['ca_url'] ?? null;

        if (!$ca_url && !empty($router_id)) {
            $ca_url = route('mikrotiks.downloadCACert', ['mikrotik' => $router_id]);
        }
        if (!$ca_url) {
            $ca_url = "https://api.example.com/tenant/$tenant_id/ca.crt";
        }

        $radius_ip = $options['radius_ip'] ?? '207.154.204.144';
        $radius_secret = $options['radius_secret'] ?? 'ZyraafSecret123';
        $api_port = $options['api_port'] ?? '8728';
        $sync_token = $options['sync_token'] ?? null;
        $sync_url = $options['sync_url'] ?? null;

        if (!$sync_url && !empty($router_id)) {
            try {
                // Generate absolute URL with full domain
                $sync_url = url(route('mikrotiks.sync', ['mikrotik' => $router_id], false));
                if ($sync_token) {
                    $sync_url .= "?token=$sync_token";
                }
            } catch (\Exception $e) {
                // Fallback: use config app.url or request URL
                $baseUrl = config('app.url') ?? (request()->scheme() . '://' . request()->getHttpHost());
                $sync_url = rtrim($baseUrl, '/') . "/mikrotiks/{$router_id}/sync";
                if ($sync_token) {
                    $sync_url .= "?token=$sync_token";
                }
            }
        }

        // Get trusted IP (server IP) - use request IP or config
        $trusted_ip = $options['trusted_ip'] ?? request()->server('SERVER_ADDR') ?? '207.154.204.144';

        // Load stub template
        $templatePath = resource_path('scripts/mikrotik_onboarding.rsc.stub');
        $template = file_exists($templatePath) ? file_get_contents($templatePath) : '';
        if (!$template) return '';

        // Replace placeholders in the template
        $replacements = [
            'name' => $name,
            'username' => $username,
            'router_password' => $router_password,
            'router_id' => $router_id,
            'tenant_id' => $tenant_id,
            'ca_url' => $ca_url ?? '',
            'radius_ip' => $radius_ip,
            'radius_secret' => $radius_secret,
            'api_port' => $api_port,
            'sync_url' => $sync_url ?? '',
            'trusted_ip' => $trusted_ip,
        ];

        foreach ($replacements as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }
}
