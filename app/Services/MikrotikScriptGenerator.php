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

        $radius_ip = $options['radius_ip'] ?? '10.0.0.1';
        $radius_secret = $options['radius_secret'] ?? 'RADIUS_SECRET';
        $sync_token = $options['sync_token'] ?? null;
        $sync_url = $options['sync_url'] ?? null;

        if (!$sync_url && !empty($router_id)) {
            $sync_url = "https://api.example.com/router/sync/$router_id";
            if ($sync_token) {
                $sync_url .= "?token=$sync_token";
            }
        }

        $trusted_ip = $options['trusted_ip'] ?? '0.0.0.0';

        // Load stub template
        $templatePath = resource_path('scripts/mikrotik_onboarding.rsc.stub');
        $template = file_exists($templatePath) ? file_get_contents($templatePath) : '';
        if (!$template) return '';

        // Replace placeholders in the template
        $replacements = compact(
            'name',
            'username',
            'router_password',
            'router_id',
            'tenant_id',
            'ca_url',
            'radius_ip',
            'radius_secret',
            'sync_url',
            'trusted_ip'
        );

        foreach ($replacements as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }
}
