<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\Radius\Check;
use App\Models\Radius\Reply;
use App\Models\Package;

class NetworkUser extends Model
{
    use HasFactory;

    protected $table = 'network_users';

    protected $fillable = [
        'account_number',
        'full_name',
        'username',
        'password',
        'phone',
        'email',
        'location',
        'type',
        'package_id',
        'status',
        'registered_at',
        'expires_at',
        'online',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'online' => 'boolean',
    ];

    protected static function booted()
    {
        // Tenant scope (created_by)
        static::addGlobalScope('created_by', function ($query) {
            if (Auth::check()) {
                $query->where('created_by', Auth::id());
            }
        });

        // Auto-fill fields
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }

            // Auto-generate account_number if not set
            if (empty($model->account_number)) {
                $tenant = app(\App\Models\Tenant::class);
                $prefix = '';

                if ($tenant && !empty($tenant->business_name)) {
                    $prefix = strtoupper(substr(preg_replace('/\s+/', '', $tenant->business_name), 0, 2));
                } else {
                    $prefix = 'NU';
                }

                do {
                    $accountNumber = $prefix . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                } while (self::where('account_number', $accountNumber)->exists());

                $model->account_number = $accountNumber;
            }
        });

        // 🔁 Sync with RADIUS on create/update/delete
        static::created(fn($user) => self::syncToRadius($user));
        static::updated(fn($user) => self::syncToRadius($user));
        static::deleted(fn($user) => self::removeFromRadius($user));
    }

    /**
     * RADIUS Synchronization
     */
    protected static function syncToRadius($user)
    {
        try {
            // ✅ Sync user password
            Check::updateOrCreate(
                ['username' => $user->username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $user->password]
            );

            // ✅ Add rate limit (if package exists)
            if ($user->package && $user->package->rate_limit) {
                Reply::updateOrCreate(
                    ['username' => $user->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $user->package->rate_limit]
                );
            }

            // ✅ Add expiration date if available
            if ($user->expires_at) {
                Reply::updateOrCreate(
                    ['username' => $user->username, 'attribute' => 'Mikrotik-Expiration'],
                    ['op' => ':=', 'value' => $user->expires_at->format('d M Y H:i:s')]
                );
            }
        } catch (\Throwable $e) {
            \Log::error("RADIUS sync failed for user {$user->username}: " . $e->getMessage());
        }
    }

    protected static function removeFromRadius($user)
    {
        try {
            Check::where('username', $user->username)->delete();
            Reply::where('username', $user->username)->delete();
        } catch (\Throwable $e) {
            \Log::error("Failed to remove RADIUS user {$user->username}: " . $e->getMessage());
        }
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * (Optional) Fetch tenant MikroTik routers
     */
    public function tenantMikrotiks()
    {
        return app('currentTenant')->mikrotiks();
    }
}
