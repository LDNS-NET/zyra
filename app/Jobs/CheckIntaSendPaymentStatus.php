<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Tenants\TenantPayment as Payment;
use IntaSend\IntaSendPHP\Collection;

class CheckIntaSendPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $paymentId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payment = Payment::find($this->paymentId);
        if (!$payment) {
            return;
        }

        // If already resolved, nothing to do
        if (in_array($payment->status, ['paid', 'failed', 'cancelled'])) {
            return;
        }

        $invoice = $payment->intasend_reference;
        if (!$invoice) {
            Log::warning('CheckIntaSendPaymentStatus: payment missing intasend_reference', ['payment_id' => $this->paymentId]);
            return;
        }

        $token = env('INTASEND_SECRET_KEY');
        $publishable_key = env('INTASEND_PUBLIC_KEY');
        $test = env('APP_ENV') !== 'production';

        try {
            $collection = new Collection();
            $collection->init([
                'token' => $token,
                'publishable_key' => $publishable_key,
                'test' => $test,
            ]);

            $response = $collection->status($invoice);
            $resp = json_decode(json_encode($response), true);
            Log::info('CheckIntaSendPaymentStatus response', ['payment_id' => $this->paymentId, 'resp' => $resp]);

            // Look for status in known locations
            $status = $resp['invoice']['state'] ?? $resp['data']['status'] ?? $resp['status'] ?? null;
            $status = $status ? strtoupper($status) : null;

            if ($status === 'PAID' || $status === 'SUCCESS' || $status === 'COMPLETED') {
                $payment->status = 'paid';
                $payment->paid_at = $payment->paid_at ?? now();
                $payment->transaction_id = $resp['invoice']['mpesa_reference'] ?? $resp['data']['transaction_id'] ?? $payment->transaction_id;
                $payment->response = array_merge($payment->response ?? [], $resp);
                $payment->save();
                return;
            }

            if ($status === 'PENDING' || $status === 'PROCESSING') {
                // still pending; requeue the job or let scheduled retries handle it
                Log::info('Payment still pending', ['payment_id' => $this->paymentId, 'status' => $status]);
                return;
            }

            // Treat everything else as failed
            $payment->status = 'failed';
            $payment->response = array_merge($payment->response ?? [], $resp);
            $payment->save();

        } catch (\Exception $e) {
            Log::error('CheckIntaSendPaymentStatus exception', ['payment_id' => $this->paymentId, 'error' => $e->getMessage()]);
            // Let the queue retry according to retry settings
            throw $e;
        }
    }
}
