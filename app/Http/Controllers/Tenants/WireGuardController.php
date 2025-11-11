<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\TenantWireguardPeer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class WireGuardController extends Controller
{
    public function registerPeer(Request $request)
    {
        $data = $request->validate([
            'public_key' => 'required|string',
            'tenant_id' => 'required',
        ]);

        // Prevent duplicate registration
        if (TenantWireguardPeer::where('public_key', $data['public_key'])->exists()) {
            return response()->json([
                'message' => 'Public key already registered.'
            ], 422);
        }

        // Allocate random available IP in 10.10.0.0/24 (reserve .0, .1, .255)
        $networkPrefix = '10.10.0.';
        $reserved = [0, 1, 255];
        $candidates = [];
        for ($i = 2; $i <= 254; $i++) {
            if (!in_array($i, $reserved)) {
                $candidates[] = $networkPrefix . $i;
            }
        }

        $used = TenantWireguardPeer::pluck('assigned_ip')->all();
        $available = array_values(array_diff($candidates, $used));
        if (empty($available)) {
            return response()->json(['message' => 'No available IPs in pool.'], 503);
        }
        $assignedIp = $available[random_int(0, count($available) - 1)];

        // Append peer block to wg0.conf
        $wgConfPath = '/etc/wireguard/wg0.conf';
        $peerBlock = "\n[Peer]\nPublicKey = {$data['public_key']}\nAllowedIPs = {$assignedIp}/32\n";

        try {
            if (!is_writable($wgConfPath)) {
                // Append via sudo tee with full paths (append-only)
                $cmd = sprintf(
                    'bash -lc %s',
                    escapeshellarg(
                        "echo -e " . escapeshellarg($peerBlock) . " | /usr/bin/sudo /usr/bin/tee -a {$wgConfPath} >/dev/null"
                    )
                );
                $process = Process::fromShellCommandline($cmd);
                $process->setTimeout(30);
                $process->run();
                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
            } else {
                file_put_contents($wgConfPath, $peerBlock, FILE_APPEND | LOCK_EX);
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to append peer to wg0.conf', 'error' => $e->getMessage()], 500);
        }

        // Reload WireGuard config via artisan command (uses sudo with full paths internally)
        try {
            Artisan::call('wireguard:reload', ['interface' => 'wg0']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to reload WireGuard', 'error' => $e->getMessage()], 500);
        }

        // Persist in DB
        $peer = new TenantWireguardPeer();
        $peer->tenant_id = (string) $data['tenant_id'];
        $peer->public_key = $data['public_key'];
        $peer->assigned_ip = $assignedIp;
        $peer->created_at = now();
        $peer->save();

        // Read server public key
        $serverPublicKeyPath = '/etc/wireguard/server_public.key';
        $serverPublicKey = @file_get_contents($serverPublicKeyPath) ?: '';
        $serverPublicKey = trim($serverPublicKey);

        // Endpoint: prefer env WG_ENDPOINT, fallback to placeholder
        $endpoint = env('WG_ENDPOINT', 'your_droplet_public_ip:51820');

        return response()->json([
            'server_public_key' => $serverPublicKey,
            'assigned_ip' => $assignedIp,
            'endpoint' => $endpoint,
        ]);
    }
}
