<?php

return [
    // WireGuard server public endpoint (IP or hostname)
    'server_endpoint' => env('WG_SERVER_ENDPOINT', ''),
    'server_public_key' => env('WG_SERVER_PUBLIC_KEY', ''),
    // Tunnel subnet used for assigning client IPs (server side must route/masquerade accordingly)
    'subnet' => env('WG_SUBNET', '10.254.0.0/16'),
    'server_port' => env('WG_SERVER_PORT', 51820),
    // Path to wg binary (if different)
    'wg_binary' => env('WG_BINARY', '/usr/bin/wg'),
    'wg_interface' => env('WG_INTERFACE', 'wg0'),
];
