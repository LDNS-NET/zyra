<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantWireguardPeer extends Model
{
    protected $table = 'tenant_wireguard_peers';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'public_key',
        'assigned_ip',
        'created_at',
    ];
}
