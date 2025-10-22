<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Tenants\TenantPayment as Payment;
use App\Models\Package;
use App\Models\Tenants\NetworkUser;
use App\Models\TenantSetting;

class ProcessSuccessfulPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $paymentId;

    public function __construct(int $paymentId)
    {
        $this->paymentId = $paymentId;
    }

    public function handle(): void
    {
        try {
            $payment = Payment::find($this->paymentId);
            if (!$payment) {
                Log::warning('ProcessSuccessfulPayment: payment not found', ['payment_id' => $this->paymentId]);
                return;
            }

            // Avoid double-processing
            if (in_array($payment->status, ['paid', 'processed'])) {
                return;
            }

            $package = Package::find($payment->package_id);

            // Create network user
            $username = 'HS' . strtoupper(\Illuminate\Support\Str::random(6));
            $plainPassword = \Illuminate\Support\Str::random(8);

            // Generate account number
            do {
                $accountNumber = 'NU' . mt_rand(1000000000, 9999999999);
            } while (NetworkUser::where('account_number', $accountNumber)->exists());

            $user = NetworkUser::create([
                'account_number' => $accountNumber,
                'username' => $username,
                'password' => bcrypt($plainPassword),
                'phone' => $payment->phone,
                'type' => 'hotspot',
                'package_id' => $package ? $package->id : null,
                'expires_at' => $package ? now()->addDays($package->duration) : null,
                'registered_at' => now(),
            ]);

            // Update payment with transaction and mark processed
            $payment->status = 'paid';
            $payment->paid_at = $payment->paid_at ?? now();
            $payment->save();

            // Send SMS if configured
            $notificationSettings = TenantSetting::where('category', 'notifications')->first()->settings ?? [];
            if (!empty($notificationSettings['payment_confirmation']['enabled'])) {
                $messageTemplate = $notificationSettings['payment_confirmation']['message'] ?? 'Thank you for your payment.';
                $placeholders = [
                    '{name}' => $user->full_name ?? $user->username,
                    '{package_name}' => $package->name ?? 'package',
                    '{amount}' => $payment->amount,
                    '{transaction_id}' => $payment->transaction_id,
                ];
                $message = str_replace(array_keys($placeholders), array_values($placeholders), $messageTemplate);
                if ($user->phone) {
                    try {
                        app(\App\Services\AfricaTalkingService::class)->sendSMS([$user->phone], $message);
                        Log::info('Sent payment confirmation SMS', ['user_id' => $user->id, 'phone' => $user->phone]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment confirmation SMS', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Disburse to tenant asynchronously via service
            $tenantId = tenant('id') ?? null;
            if ($tenantId) {
                app(\App\Services\TenantPayoutService::class)->disburse($payment->amount, $tenantId, ['payment_id' => $payment->id]);
            }

            Log::info('Processed successful payment', ['payment_id' => $payment->id, 'user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('ProcessSuccessfulPayment exception', ['payment_id' => $this->paymentId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
