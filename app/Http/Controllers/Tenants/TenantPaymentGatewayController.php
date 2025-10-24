<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantPaymentGateway;
use App\Models\Tenant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class TenantPaymentGatewayController extends Controller
{
    public function edit(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user()?->tenant_id ?? null);

        if (!$tenantId && app()->environment('local')) {
            $tenantId = Tenant::first()?->id;
        }

        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }

        $gateways = Cache::remember("tenant_payment_gateways_{$tenantId}", 60, function () use ($tenantId) {
            return TenantPaymentGateway::where('tenant_id', $tenantId)->get();
        });

        return Inertia::render('Settings/Payment/Payment', [
            'gateways' => $gateways,
            'phone_number' => $request->user()?->phone ?? '',
        ]);
    }

    public function update(Request $request)
    {
        $tenantId = tenant('id') ?? ($request->user()?->tenant_id ?? null);

        if (!$tenantId && app()->environment('local')) {
            $tenantId = Tenant::first()?->id;
        }

        if (!$tenantId) {
            abort(400, 'No tenant context available');
        }

        $validated = $request->validate([
            'provider' => 'required|in:intasend,mpesa,bank,custom',
            'payout_method' => 'required|in:bank,mpesa_phone,till,paybill',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:20',
            'till_number' => 'nullable|string|max:20',
            'paybill_business_number' => 'nullable|string|max:20',
            'paybill_account_number' => 'nullable|string|max:20',
            'label' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        TenantPaymentGateway::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'provider' => $validated['provider'],
                'payout_method' => $validated['payout_method'],
            ],
            array_merge($validated, [
                'created_by' => auth()->id(),
                'last_updated_by' => auth()->id(),
            ])
        );

        Cache::forget("tenant_payment_gateways_{$tenantId}");

        return redirect()->back()->with('success', 'Payment gateway updated successfully.');
    }
}
