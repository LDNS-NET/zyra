<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TenantSMS;
use App\Models\Tenants\NetworkUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class SMSController extends Controller
{
    public function index(Request $request)
{
    $perPage = (int) $request->input('perPage', 10);

    $smsLogs = TenantSMS::latest()->paginate($perPage)->withQueryString();

    return Inertia::render('SMS/Index', [
        'smsLogs' => $smsLogs,
        'perPage' => (int) $perPage,
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
        // ...existing code...

        $validated = $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'exists:network_users,id',
            'message' => 'required|string|max:500',
        ]);

        $renters = NetworkUser::whereIn('id', $validated['recipients'])->get();

        if ($renters->isEmpty()) {
            return redirect()->back()->withErrors(['recipients' => 'No valid renters selected.']);
        }

        $logIds = [];
        $phoneNumbers = [];

        foreach ($renters as $renter) {
            $smsLog = TenantSMS::create([
                'recipient_name' => $renter->full_name,
                // in this codebase the phone column is named `phone` on NetworkUser, but the TenantSMS uses `phone_number`
                'phone_number' => $renter->phone ?? $renter->phone_number ?? null,
                'message' => $validated['message'],
                'status' => 'pending',
            ]);
            $logIds[] = $smsLog->id;
            // normalize phone number from whichever field is present
            $rawPhone = $renter->phone ?? $renter->phone_number ?? '';
            $phoneNumbers[] = preg_replace('/^0/', '254', trim($rawPhone));
        }

        $phoneNumbersString = implode(',', $phoneNumbers);

        $this->sendSms($logIds, $phoneNumbersString, $validated['message']);

        return redirect()->route('sms.index')
            ->with('success', 'SMS batch is being processed.');
    }

    public function destroy(TenantSMS $smsLog)
    {
        $smsLog->delete();

        return redirect()->route('sms.index')
            ->with('success', 'SMS log deleted successfully.');
    }

    private function sendSms(array $logIds, string $phoneNumbers, string $message)
    {
        try {
            $apiKey = env('TALKSASA_API_KEY');
            $senderId = env('TALKSASA_SENDER_ID');

            if (!$apiKey || !$senderId) {
                TenantSMS::whereIn('id', $logIds)->update([
                    'status' => 'failed',
                    'error_message' => 'Missing TalkSasa API credentials'
                ]);
                return;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://bulksms.talksasa.com/api/v3/sms/send', [
                'recipient' => $phoneNumbers,
                'sender_id' => $senderId,
                'type' => 'plain',
                'message' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 'success') {
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
