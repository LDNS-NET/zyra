<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\Radius\Radcheck;
use App\Models\Radius\Radreply;
use App\Models\Radius\Radusergroup;

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

    public function package(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Package::class, 'package_id');
    }

    public function tenantMikrotiks()
    {
        return app('currentTenant')->mikrotiks();
    }

    protected static function booted()
    {
        static::addGlobalScope('created_by', function ($query) {
            if (Auth::check()) {
                $query->where('created_by', Auth::id());
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }

            // Auto-generate account_number
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

        /**
         * 🔥 Sync with RADIUS database automatically
         */
        static::created(function ($user) {
            // Create user credentials
            Radcheck::create([
                'username'  => $user->username,
                'attribute' => 'Cleartext-Password',
                'op'        => ':=',
                'value'     => $user->password,
            ]);

            // Assign package speeds (if package is set)
            if ($user->package && $user->package->speed) {
                Radreply::create([
                    'username'  => $user->username,
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op'        => ':=',
                    'value'     => $user->package->speed,
                ]);
            }

            // Optionally assign to default group
            Radusergroup::create([
                'username'  => $user->username,
                'groupname' => 'default',
                'priority'  => 1,
            ]);
        });

        static::updated(function ($user) {
            // Update password
            Radcheck::where('username', $user->username)
                ->update(['value' => $user->password]);

            // Update package speed
            if ($user->package && $user->package->speed) {
                Radreply::updateOrCreate(
                    ['username' => $user->username, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $user->package->speed]
                );
            }
        });

        static::deleted(function ($user) {
            Radcheck::where('username', $user->username)->delete();
            Radreply::where('username', $user->username)->delete();
            Radusergroup::where('username', $user->username)->delete();
        });
    }
}
