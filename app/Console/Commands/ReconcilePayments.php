<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Services\Eps\EpsException;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WebhookNotifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Safety net, scheduled every 5 minutes:
 *  1. asks EPS about attempts with no result yet (customer closed the tab after paying …),
 *  2. expires checkout links nobody paid,
 *  3. retries webhooks the apps did not accept.
 */
#[Signature('payments:reconcile')]
#[Description('Verify open EPS attempts, expire old checkouts and retry failed webhooks')]
class ReconcilePayments extends Command
{
    public function handle(PaymentService $payments, WebhookNotifier $webhooks): int
    {
        $verified = 0;

        PaymentAttempt::with('payment.merchantApp')
            ->where('status', 'initiated')
            ->whereNotNull('redirect_url')
            ->where('created_at', '<=', now()->subMinutes(2))
            ->where('created_at', '>=', now()->subDays(3))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (PaymentAttempt $attempt) use ($payments, &$verified) {
                try {
                    // EPS shows an unpaid transaction as "Cancel" from the start, so
                    // only after 3 hours without "Success" is the attempt given up.
                    $payments->verify($attempt, giveUp: $attempt->created_at->lt(now()->subHours(3)));
                    $verified++;
                } catch (EpsException $e) {
                    $this->warn("{$attempt->merchant_transaction_id}: {$e->getMessage()}");
                }
            });

        $expired = 0;
        Payment::whereIn('status', ['pending', 'failed', 'cancelled'])
            ->where('expires_at', '<', now())
            ->limit(500)
            ->get()
            ->each(function (Payment $payment) use ($payments, &$expired) {
                $expired += (int) $payments->expire($payment);
            });

        $resent = 0;
        Payment::with('merchantApp')
            ->whereIn('webhook_status', ['pending', 'failed'])
            ->whereNotNull('webhook_next_attempt_at')
            ->where('webhook_next_attempt_at', '<=', now())
            ->limit(100)
            ->get()
            ->each(function (Payment $payment) use ($webhooks, &$resent) {
                $resent += (int) $webhooks->send($payment);
            });

        $this->info("Verified {$verified} attempt(s), expired {$expired} checkout(s), delivered {$resent} webhook(s).");

        return self::SUCCESS;
    }
}
