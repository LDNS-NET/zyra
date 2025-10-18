<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Tenants\TenantSMS;
use App\Models\Tenants\NetworkUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class TenantSMSController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);

        $smsLogs = TenantSMS::latest()->paginate($perPage)->withQueryString();

        return Inertia::render('SMS/Index', [
            'smsLogs' => $smsLogs,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        $renters = NetworkUser::all();

        return Inertia::render('SMS/Create', [
            'renters' => $renters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'exists:network_users,id',
            'message' => 'required|string|max:500',
        ]);

        $renters = NetworkUser::whereIn('id', $validated['recipients'])->get();

        if ($renters->isEmpty()) {
            return back()->withErrors(['recipients' => 'No valid recipients selected.']);
        }

        $logIds = [];
        $phoneNumbers = [];

        foreach ($renters as $renter) {
            $smsLog = TenantSMS::create([
                'user_id' => auth()->id(),
                'recipient_name' => $renter->full_name ?? 'Unknown',
                'phone_number' => $renter->phone,
                'message' => $validated['message'],
                'status' => 'pending',
            ]);

            $logIds[] = $smsLog->id;

            // Normalize Kenyan phone numbers
            $formatted = preg_replace('/\s+/', '', $renter->phone);
            if (str_starts_with($formatted, '+254')) {
                $formatted = substr($formatted, 1);
            } elseif (str_starts_with($formatted, '0')) {
                $formatted = '254' . substr($formatted, 1);
            }
            $phoneNumbers[] = $formatted;
        }

        $this->sendSms($logIds, implode(',', $phoneNumbers), $validated['message']);

        return redirect()->route('sms.index')
            ->with('success', 'SMS batch is being processed.');
    }

    public function destroy(TenantSMS $smsLog)
    {
        $smsLog->delete();

        return redirect()->route('sms.index')
            ->with('success', 'SMS log deleted successfully.');
    }

    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids', []);
        TenantSMS::whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected SMS logs deleted successfully.');
    }

    public function show(TenantSMS $smsLog)
    {
        return Inertia::render('SMS/Show', [
            'smsLog' => $smsLog,
        ]);
    }

    private function sendSms(array $logIds, string $phoneNumbers, string $message)
    {
        try {
            $apiKey = env('TALKSASA_API_KEY');
            $senderId = env('TALKSASA_SENDER_ID');

            if (!$apiKey || !$senderId) {
                TenantSMS::whereIn('id', $logIds)->update([
                    'status' => 'failed',
                    'error_message' => 'Missing TalkSasa API credentials.',
                ]);
                return;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://bulksms.talksasa.com/api/v3/sms/send', [
                'recipients' => $phoneNumbers,
                'sender_id' => $senderId,
                'type' => 'plain',
                'message' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? null) === 'success') {
                TenantSMS::whereIn('id', $logIds)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } else {
                TenantSMS::whereIn('id', $logIds)->update([
                    'status' => 'failed',
                    'error_message' => $data['message'] ?? $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            TenantSMS::whereIn('id', $logIds)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
