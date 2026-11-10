<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

/**
 * Tells the app about a payment status change with a signed server-to-server POST.
 * Failed deliveries are retried by `php artisan payments:reconcile` (scheduled).
 */
class WebhookNotifier
{
    public function url(Payment $payment): ?string
    {
        $payment->loadMissing('merchantApp');

        return $payment->webhook_url ?: $payment->merchantApp?->webhook_url;
    }

    public function send(Payment $payment): bool
    {
        $payment->loadMissing('merchantApp');
        $url = $this->url($payment);

        if (blank($url)) {
            $payment->forceFill(['webhook_status' => 'none', 'webhook_next_attempt_at' => null])->save();

            return false;
        }

        $event = 'payment.'.$payment->status;
        $body = json_encode([
            'event' => $event,
            'sent_at' => now()->toIso8601String(),
            'data' => $payment->toApiArray(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = time();

        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withHeaders([
                    'X-Event' => $event,
                    'X-App-Key' => $payment->merchantApp->app_key,
                    'X-Timestamp' => (string) $timestamp,
                    'X-Signature' => Signature::make($payment->merchantApp->app_secret, [$timestamp, $body]),
                    'User-Agent' => 'PaymentHub-Webhook/1.0',
                ])
                ->withBody($body, 'application/json')
                ->post($url);

            $ok = $response->successful();
            $note = 'HTTP '.$response->status().': '.mb_substr(trim((string) $response->body()), 0, 500);
        } catch (\Throwable $e) {
            $ok = false;
            $note = 'Error: '.mb_substr($e->getMessage(), 0, 500);
        }

        $attempts = $payment->webhook_attempts + 1;
        $backoff = config('payment.webhook_backoff', []);
        $retryIn = $backoff[$attempts - 1] ?? null;

        $payment->forceFill([
            'webhook_status' => $ok ? 'delivered' : 'failed',
            'webhook_attempts' => $attempts,
            'webhook_last_attempt_at' => now(),
            'webhook_next_attempt_at' => (! $ok && $retryIn) ? now()->addMinutes($retryIn) : null,
            'webhook_last_response' => $note,
        ])->save();

        return $ok;
    }
}
