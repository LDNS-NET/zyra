<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\TenantSmsGateway;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TenantSmsGatewayController extends Controller
{
    /**
     * Resolve tenant ID from tenancy context or user.
     */
    protected function getTenantId(Request $request): string
    {
        $tenantId = tenant('id') ?? optional($request->user())->tenant_id;

        if (!$tenantId && app()->environment('local')) {
            $tenantId = Tenant::first()?->id;
        }

        abort_if(!$tenantId, 400, 'No tenant context available.');

        return $tenantId;
    }

    /**
     * Display tenant SMS gateway settings.
     */
    public function edit(Request $request)
    {
        $tenantId = $this->getTenantId($request);

        $gateways = TenantSmsGateway::where('tenant_id', $tenantId)->get();

        return Inertia::render('Settings/SMS/SmsGateway', [
            'gateways' => $gateways,
        ]);
    }

    /**
     * Store or update a tenant's SMS gateway configuration.
     */
    public function update(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $provider = Str::lower(trim($request->input('provider', '')));

        // --- Validation Rules ---
        $rules = [
            'provider' => ['required', Rule::in([
                'talksasa', 'bytewave', 'africastalking',
                'textsms', 'mobitech', 'twilio', 'custom',
            ])],
            'label' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ];

        // Provider-specific fields
        $rules += match ($provider) {
            'talksasa', 'bytewave' => [
                'api_key' => 'required|string',
                'sender_id' => 'required|string',
            ],
            'africastalking', 'textsms', 'mobitech' => [
                'username' => 'required|string',
                'api_key' => 'required|string',
                'sender_id' => 'required|string',
            ],
            'twilio' => [
                'username' => 'required|string',
                'api_secret' => 'required|string',
                'sender_id' => 'required|string',
            ],
            default => [
                'api_key' => 'required|string',
            ],
        };

        $data = $request->validate($rules);
        $data['provider'] = $provider;

        // --- Default gateway handling ---
        $hasDefault = TenantSmsGateway::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->exists();

        $data['is_default'] = $request->boolean('is_default')
            ?: (!$hasDefault && $provider === 'talksasa');

        if ($data['is_default']) {
            TenantSmsGateway::where('tenant_id', $tenantId)
                ->where('provider', '!=', $provider)
                ->update(['is_default' => false]);
        }

        // --- Save or Update Gateway ---
        TenantSmsGateway::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'provider' => $provider,
            ],
            [
                'label' => $data['label'] ?? ucfirst($provider),
                'api_key' => $data['api_key'] ?? null,
                'api_secret' => $data['api_secret'] ?? null,
                'username' => $data['username'] ?? null,
                'sender_id' => $data['sender_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
            ]
        );

        cache()->forget("tenant_sms_gateways_{$tenantId}");

        return back()->with('success', 'SMS gateway settings updated successfully.');
    }

    /**
     * Return all tenant SMS gateways as JSON.
     */
    public function json(Request $request)
    {
        $tenantId = $this->getTenantId($request);

        $gateways = TenantSmsGateway::where('tenant_id', $tenantId)->get();

        return response()->json([
            'gateways' => $gateways,
        ]);
    }
}
