<?php

namespace App\Services\Payments;

use App\Models\MerchantApp;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Services\Eps\EpsClient;
use App\Services\Eps\EpsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The payment life cycle:
 *
 *   create()  app → API         payment "pending", app gets checkout_url
 *   start()   customer submits  new attempt at EPS, payment "processing", customer sent to EPS
 *   verify()  EPS sends back /   ask EPS for the real result → "paid" / "failed" / "cancelled",
 *             reconcile job     then webhook the app
 *
 * The browser redirect from EPS is never trusted on its own: only
 * CheckMerchantTransactionStatus decides whether money was received.
 */
class PaymentService
{
    public function __construct(
        protected EpsClient $eps,
        protected WebhookNotifier $webhooks,
    ) {}

    public const CUSTOMER_FIELDS = ['name', 'email', 'phone', 'address', 'city', 'state', 'postcode', 'country'];

    /** @param  array<string, mixed>  $data  validated API input */
    public function create(MerchantApp $app, array $data, ?string $ip = null): Payment
    {
        $customer = [];
        foreach (self::CUSTOMER_FIELDS as $field) {
            $customer['customer_'.$field] = filled($data['customer'][$field] ?? null) ? trim((string) $data['customer'][$field]) : null;
        }

        return $app->payments()->create([
            'order_id' => $data['order_id'],
            'amount' => round((float) $data['amount'], 2),
            'currency' => config('payment.currency', 'BDT'),
            'description' => $data['description'],
            'return_url' => $data['return_url'],
            'cancel_url' => $data['cancel_url'] ?? null,
            'webhook_url' => $data['webhook_url'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'status' => 'pending',
            'ip_address' => $ip,
            'expires_at' => now()->addMinutes(config('payment.checkout_expires_minutes', 120)),
        ] + $customer);
    }

    /**
     * Save the customer's details and open a new EPS transaction.
     *
     * @param  array<string, string|null>  $customer  name, email, phone, address, city, state, postcode, country
     */
    public function start(Payment $payment, array $customer, ?string $ip = null): PaymentAttempt
    {
        $attempt = DB::transaction(function () use ($payment, $customer) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! $locked->canBePaid()) {
                throw new PaymentException($locked->isPaid()
                    ? 'This payment has already been completed.'
                    : 'This payment link has expired. Please start again from the app.');
            }

            foreach (self::CUSTOMER_FIELDS as $field) {
                if (array_key_exists($field, $customer)) {
                    $locked->{'customer_'.$field} = filled($customer[$field]) ? trim((string) $customer[$field]) : null;
                }
            }
            $locked->save();

            return $locked->attempts()->create([
                'merchant_transaction_id' => PaymentAttempt::newMerchantTransactionId(),
                'status' => 'initiated',
            ]);
        });

        $payment->refresh();

        try {
            $result = $this->eps->initialize($this->epsPayload($payment, $attempt, $ip));
        } catch (EpsException $e) {
            $attempt->update(['status' => 'error', 'init_response' => $e->response ?: ['error' => $e->getMessage()]]);
            Log::warning('EPS initialize failed', ['payment' => $payment->reference, 'error' => $e->getMessage()]);

            throw $e;
        }

        $attempt->update([
            'gateway_transaction_id' => $result['transaction_id'],
            'redirect_url' => $result['redirect_url'],
            'init_response' => $result['raw'],
        ]);

        Payment::whereKey($payment->id)->where('status', '!=', 'paid')->update([
            'status' => 'processing',
            'merchant_transaction_id' => $attempt->merchant_transaction_id,
            'gateway_transaction_id' => $result['transaction_id'],
            'failure_reason' => null,
            'updated_at' => now(),
        ]);

        return $attempt;
    }

    /**
     * Ask EPS for the result of an attempt and update the payment.
     *
     * @param  string|null  $returnResult  where EPS sent the customer: success / fail / cancel
     * @param  bool  $giveUp  treat "no result yet" as failed (attempt abandoned)
     */
    public function verify(PaymentAttempt $attempt, ?string $returnResult = null, bool $giveUp = false): Payment
    {
        $response = $this->eps->status($attempt->merchant_transaction_id);
        [$outcome, $reason] = $this->outcome($response, $attempt);

        /*
         * Only "Success" is decisive on its own. EPS reports "Cancel" for a
         * transaction the customer is still paying on, so a non-success
         * status ends the attempt only when EPS sent the customer to the
         * fail/cancel URL, or when the attempt is abandoned ($giveUp).
         */
        if ($outcome === 'pending' && in_array($returnResult, ['fail', 'cancel'], true)) {
            $outcome = $returnResult === 'cancel' ? 'cancelled' : 'failed';
            $reason ??= $returnResult === 'cancel' ? 'Cancelled at the payment page' : 'Payment was not completed';
        }

        if ($outcome === 'pending' && $giveUp) {
            $outcome = 'failed';
            $reason = 'Not completed at EPS'.($reason ? " ({$reason})" : '');
        }

        $changed = DB::transaction(function () use ($attempt, $response, $outcome, $reason, $returnResult) {
            $payment = Payment::whereKey($attempt->payment_id)->lockForUpdate()->firstOrFail();
            $attempt = PaymentAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            $attempt->fill([
                'status' => match ($outcome) {
                    'success' => 'success',
                    'failed' => 'failed',
                    'cancelled' => 'cancelled',
                    default => 'initiated',
                },
                'gateway_status' => Str::limit((string) EpsClient::value($response, 'Status'), 250, ''),
                'verify_response' => $response,
                'verified_at' => now(),
                'return_result' => $returnResult ?? $attempt->return_result,
            ])->save();

            if ($outcome === 'success') {
                if ($payment->isPaid()) {
                    if ($payment->merchant_transaction_id !== $attempt->merchant_transaction_id) {
                        Log::critical('Payment received twice — refund one attempt', [
                            'payment' => $payment->reference,
                            'second_attempt' => $attempt->merchant_transaction_id,
                        ]);
                    }

                    return false;
                }

                $feePercent = (float) ($payment->merchantApp?->fee_percent ?? 0);
                $fee = round((float) $payment->amount * $feePercent / 100, 2);

                $payment->fill([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'fee_amount' => $fee,
                    'net_amount' => round((float) $payment->amount - $fee, 2),
                    'merchant_transaction_id' => $attempt->merchant_transaction_id,
                    // EPSTransactionId (from the status call) is the id EPS support and the EPS portal use.
                    'gateway_transaction_id' => EpsClient::value($response, 'EPSTransactionId')
                        ?: ($attempt->gateway_transaction_id ?: $payment->gateway_transaction_id),
                    'payment_method' => Str::limit((string) EpsClient::value($response, 'FinancialEntity'), 250, '') ?: null,
                    'failure_reason' => null,
                ]);
                $this->queueWebhook($payment);
                $payment->save();

                return true;
            }

            $isLatest = ! $payment->attempts()->where('id', '>', $attempt->id)->exists();

            if (in_array($outcome, ['failed', 'cancelled'], true) && ! $payment->isPaid() && $isLatest
                && in_array($payment->status, ['pending', 'processing'], true)) {
                $payment->fill(['status' => $outcome, 'failure_reason' => Str::limit((string) $reason, 250, '') ?: null]);
                $this->queueWebhook($payment);
                $payment->save();

                return true;
            }

            return false;
        });

        $payment = $attempt->payment()->first();

        if ($changed) {
            $this->webhooks->send($payment);
        }

        return $payment->fresh();
    }

    /** Re-check the newest open attempt, if any. Never throws. */
    public function refresh(Payment $payment): Payment
    {
        // Only attempts that reached EPS (an attempt cut off before EPS answered has no redirect_url).
        $attempt = $payment->attempts()->where('status', 'initiated')->whereNotNull('redirect_url')->first();

        if (! $attempt || $payment->isPaid()) {
            return $payment;
        }

        try {
            return $this->verify($attempt);
        } catch (EpsException $e) {
            Log::info('EPS status check failed', ['payment' => $payment->reference, 'error' => $e->getMessage()]);

            return $payment;
        }
    }

    /** The customer left the checkout page and went back to the app. */
    public function cancel(Payment $payment): Payment
    {
        $payment = $this->refresh($payment);

        $changed = DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, ['pending', 'processing', 'failed'], true)) {
                return false;
            }

            $locked->fill(['status' => 'cancelled', 'failure_reason' => 'Cancelled by customer']);
            $this->queueWebhook($locked);
            $locked->save();

            return true;
        });

        $payment->refresh();

        if ($changed) {
            $this->webhooks->send($payment);
        }

        return $payment;
    }

    public function expire(Payment $payment): bool
    {
        $changed = Payment::whereKey($payment->id)
            ->whereIn('status', ['pending', 'failed', 'cancelled'])
            ->update(['status' => 'expired', 'webhook_status' => 'pending', 'webhook_attempts' => 0, 'updated_at' => now()]) > 0;

        if ($changed) {
            $this->webhooks->send($payment->refresh());
        }

        return $changed;
    }

    /**
     * Signed link back to the app: …?reference=&order_id=&status=&timestamp=&signature=
     */
    public function returnUrl(Payment $payment, bool $cancelled = false): string
    {
        $payment->loadMissing('merchantApp');
        $base = $cancelled ? ($payment->cancel_url ?: $payment->return_url) : $payment->return_url;
        $timestamp = time();

        $params = [
            'reference' => $payment->reference,
            'order_id' => $payment->order_id,
            'status' => $payment->status,
            'timestamp' => $timestamp,
            'signature' => Signature::make($payment->merchantApp->app_secret, [
                $timestamp, $payment->reference, $payment->order_id, $payment->status,
            ]),
        ];

        $fragment = '';
        if (str_contains($base, '#')) {
            [$base, $fragment] = explode('#', $base, 2);
            $fragment = '#'.$fragment;
        }

        return $base.(str_contains($base, '?') ? '&' : '?').http_build_query($params).$fragment;
    }

    protected function queueWebhook(Payment $payment): void
    {
        $payment->webhook_status = 'pending';
        $payment->webhook_attempts = 0;
        $payment->webhook_next_attempt_at = now();
    }

    /**
     * @return array{0: string, 1: string|null} outcome (success|failed|cancelled|pending) and reason
     */
    protected function outcome(array $response, PaymentAttempt $attempt): array
    {
        $status = Str::lower(trim((string) EpsClient::value($response, 'Status')));
        $error = EpsClient::value($response, 'ErrorMessage');
        $epsMtid = (string) EpsClient::value($response, 'MerchantTransactionId');

        if ($epsMtid !== '' && $epsMtid !== $attempt->merchant_transaction_id) {
            return ['pending', 'EPS answered for a different transaction'];
        }

        if (in_array($status, ['success', 'successful', 'succeeded', 'paid', 'completed', 'complete'], true)) {
            $paid = (float) str_replace(',', '', (string) EpsClient::value($response, 'TotalAmount'));
            $expected = (float) $attempt->payment->amount;

            if (abs($paid - $expected) > 0.009) {
                Log::critical('EPS amount does not match the payment', [
                    'payment' => $attempt->payment->reference,
                    'expected' => $expected,
                    'eps' => $paid,
                ]);

                return ['failed', 'Amount mismatch: EPS reported '.$paid];
            }

            return ['success', null];
        }

        // "Cancel", "Failed", empty … : not paid (yet). The caller decides whether that is final.
        $label = trim((string) EpsClient::value($response, 'Status'));

        return ['pending', $error ?: ($label !== '' ? 'EPS status: '.$label : null)];
    }

    /** @return array<string, mixed> InitializeEPS body (merchantId / storeId are added by EpsClient) */
    protected function epsPayload(Payment $payment, PaymentAttempt $attempt, ?string $ip): array
    {
        $payment->loadMissing('merchantApp');
        $mtid = $attempt->merchant_transaction_id;
        $appName = Str::limit($payment->merchantApp->name, 100, '');
        $product = Str::limit($payment->description, 100, '');
        $amount = number_format((float) $payment->amount, 2, '.', '');
        $number = $payment->attempts()->count();

        $city = (string) $payment->customer_city;
        $state = $payment->customer_state ?: $city;
        $country = $payment->customer_country ?: 'BD';
        if (in_array(Str::lower(trim($country)), ['bangladesh', 'bd', 'bgd'], true)) {
            $country = 'BD'; // EPS samples use the ISO code
        }

        return [
            'CustomerOrderId' => $payment->reference.'-'.$number,
            'merchantTransactionId' => $mtid,
            'transactionTypeId' => $this->eps->transactionTypeId(),
            'financialEntityId' => 0,
            'transitionStatusId' => 0,
            'totalAmount' => (float) $amount,
            'ipAddress' => $ip ?: '127.0.0.1',
            'version' => '1',
            'successUrl' => route('checkout.eps-return', ['attempt' => $mtid, 'result' => 'success']),
            'failUrl' => route('checkout.eps-return', ['attempt' => $mtid, 'result' => 'fail']),
            'cancelUrl' => route('checkout.eps-return', ['attempt' => $mtid, 'result' => 'cancel']),
            'customerName' => (string) $payment->customer_name,
            'customerEmail' => (string) $payment->customer_email,
            'customerAddress' => (string) $payment->customer_address,
            'customerAddress2' => '',
            'customerCity' => $city,
            'customerState' => $state,
            'customerPostcode' => (string) $payment->customer_postcode,
            'customerCountry' => $country,
            'customerPhone' => (string) $payment->customer_phone,
            'shipmentName' => (string) $payment->customer_name,
            'shipmentAddress' => (string) $payment->customer_address,
            'shipmentAddress2' => '',
            'shipmentCity' => $city,
            'shipmentState' => $state,
            'shipmentPostcode' => (string) $payment->customer_postcode,
            'shipmentCountry' => $country,
            'valueA' => $payment->reference,
            'valueB' => Str::limit($payment->order_id, 100, ''),
            'valueC' => $appName,
            'valueD' => '',
            'shippingMethod' => 'NO',
            'noOfItem' => '1',
            'productName' => $product,
            'productProfile' => 'general',
            'productCategory' => $appName,
            'ProductList' => [[
                'ProductName' => $product,
                'NoOfItem' => '1',
                'ProductProfile' => 'general',
                'ProductCategory' => $appName,
                'ProductPrice' => $amount,
            ]],
        ];
    }
}
