<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantSmsGateway;
use Inertia\Inertia;

class TenantSmsGatewayController extends Controller
{
    protected function getTenantId(Request $request)
    {
        $tenantId = tenant('id') ?? optional($request->user())->tenant_id;
        if (!$tenantId && app()->environment('local')) {
            $tenantId = \App\Models\Tenant::first()?->id;
        }

        abort_if(!$tenantId, 400, 'No tenant context available');
        return $tenantId;
    }

    public function edit(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $gateways = TenantSmsGateway::where('tenant_id', $tenantId)->get();

        return Inertia::render('Settings/SMS/SmsGateway', compact('gateways'));
    }

    public function update(Request $request)
    {
        $tenantId = $this->getTenantId($request);

        $rules = [
            'provider' => 'required|in:talksasa,bytewave,africastalking,textsms,mobitech,twilio,custom',
            'label' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ];

        $provider = $request->provider;
        $rules += match ($provider) {
            'talksasa', 'bytewave' => ['api_key' => 'required', 'sender_id' => 'required'],
            'africastalking', 'textsms', 'mobitech' => ['username' => 'required', 'api_key' => 'required', 'sender_id' => 'required'],
            default => ['api_key' => 'required', 'api_secret' => 'required'],
        };

        $data = $request->validate($rules);

        $data['is_default'] = $request->boolean('is_default') ?: !TenantSmsGateway::where('tenant_id', $tenantId)->where('is_default', true)->exists();

        if ($data['is_default']) {
            TenantSmsGateway::where('tenant_id', $tenantId)
                ->where('provider', '!=', $data['provider'])
                ->update(['is_default' => false]);
        }

        $gateway = TenantSmsGateway::updateOrCreate(
            ['tenant_id' => $tenantId, 'provider' => $data['provider']],
            array_merge($data, [
                'label' => $data['label'] ?? ucfirst($data['provider'])." ($tenantId)",
            ])
        );

        cache()->forget("tenant_sms_gateways_{$tenantId}");

        return back()->with('success', 'SMS gateway settings updated successfully.');
    }

    public function json(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $gateways = TenantSmsGateway::where('tenant_id', $tenantId)->get();

        return response()->json(['gateways' => $gateways]);
    }
}
