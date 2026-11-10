<?php

namespace Tests\Feature;

use App\Filament\Resources\Disbursements\Pages\ManageDisbursements;
use App\Models\Disbursement;
use App\Models\MerchantApp;
use App\Models\Payment;
use App\Models\User;
use App\Services\Eps\EpsClient;
use App\Services\Payments\DisbursementService;
use App\Services\Payments\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentHubTest extends TestCase
{
    use RefreshDatabase;

    protected MerchantApp $merchant;

    protected string $epsStatus = 'Success';

    protected string $epsAmount = '1000.00';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payment.eps' => array_merge(config('payment.eps'), [
                'sandbox' => true,
                'base_url' => null,
                'merchant_id' => 'merchant-1',
                'store_id' => 'store-1',
                'username' => 'user@example.com',
                'password' => 'secret',
                'hash_key' => 'test-hash-key',
            ]),
        ]);

        $this->merchant = MerchantApp::create([
            'name' => 'Test ERP',
            'webhook_url' => 'https://erp.test/webhook/central-pay',
            'allowed_hosts' => ['erp.test'],
            'fee_percent' => 2,
        ]);

        Http::fake(function (HttpRequest $request) {
            $url = $request->url();

            if (str_contains($url, '/v1/Auth/GetToken')) {
                return Http::response(['token' => 'tok-123', 'expireDate' => now()->addHour()->toIso8601String(), 'errorMessage' => null, 'errorCode' => null]);
            }
            if (str_contains($url, '/v1/EPSEngine/InitializeEPS')) {
                return Http::response(['TransactionId' => 'eps-txn-1', 'RedirectURL' => 'https://pg.eps.com.bd/PG?data=eps-txn-1', 'ErrorMessage' => '', 'ErrorCode' => null]);
            }
            if (str_contains($url, '/v1/EPSEngine/CheckMerchantTransactionStatus')) {
                parse_str(parse_url($url, PHP_URL_QUERY), $q);

                return Http::response([
                    'MerchantTransactionId' => $q['merchantTransactionId'],
                    'EPSTransactionId' => '32617271711105',
                    'Status' => $this->epsStatus,
                    'TotalAmount' => $this->epsAmount,
                    'FinancialEntity' => 'bKash',
                ]);
            }
            if (str_starts_with($url, 'https://erp.test/')) {
                return Http::response(['ok' => true]);
            }

            return Http::response('unexpected', 500);
        });
    }

    public function test_eps_hash_matches_the_example_in_the_eps_guide(): void
    {
        $eps = new EpsClient(['hash_key' => 'SFNLQHJlY2lwZXdhbGEjYTc3Zi1mOTQ5NWZhY2M2ZTZuZXQ=']);

        $this->assertSame(
            'BKXUD5z0NQgPDQMcZuPL2dSUwo5oSdvBpzz2xbkxikB7KfYV0kZIF8sW6udvSqOTZNUJ5VHnMTSJP3oxDABpJQ==',
            $eps->hash('dt_merchant@eps.com.bd'),
        );
    }

    public function test_api_rejects_unsigned_and_badly_signed_requests(): void
    {
        $this->postJson('/api/v1/payments', $this->payload())->assertUnauthorized();

        $this->signed('POST', '/api/v1/payments', $this->payload(), secret: 'wrong')->assertUnauthorized();

        $this->signed('POST', '/api/v1/payments', $this->payload(), timestamp: time() - 3600)->assertUnauthorized();

        $this->merchant->update(['is_active' => false]);
        $this->signed('POST', '/api/v1/payments', $this->payload())->assertUnauthorized();
    }

    public function test_api_creates_a_checkout_and_replays_the_same_order(): void
    {
        $response = $this->signed('POST', '/api/v1/payments', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.amount', '1000.00')
            ->assertJsonPath('data.metadata.business_id', 5);

        $reference = $response->json('data.reference');
        $this->assertStringEndsWith('/pay/'.$reference, $response->json('data.checkout_url'));

        // Same order and amount: same payment back.
        $this->signed('POST', '/api/v1/payments', $this->payload())->assertOk()->assertJsonPath('data.reference', $reference);

        // Same order, other amount: refused.
        $this->signed('POST', '/api/v1/payments', $this->payload(['amount' => 5]))->assertStatus(409);

        $this->signed('GET', '/api/v1/payments/'.$reference)->assertOk()->assertJsonPath('data.order_id', 'SUB-5-10-1');
        $this->signed('GET', '/api/v1/orders/SUB-5-10-1')->assertOk()->assertJsonPath('data.reference', $reference);
    }

    public function test_api_validates_input_and_allowed_hosts(): void
    {
        $this->signed('POST', '/api/v1/payments', $this->payload(['amount' => 0, 'description' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'description']);

        $this->signed('POST', '/api/v1/payments', $this->payload(['return_url' => 'https://evil.test/return']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['return_url']);
    }

    public function test_another_app_cannot_read_a_payment(): void
    {
        $payment = $this->createPayment();
        $other = MerchantApp::create(['name' => 'Other']);

        $this->signed('GET', '/api/v1/payments/'.$payment->reference, app: $other)->assertNotFound();
    }

    public function test_full_checkout_flow_marks_paid_notifies_app_and_redirects_back(): void
    {
        $payment = $this->createPayment();

        $this->get('/pay/'.$payment->reference)->assertOk()->assertSee('Complete your payment')->assertSee('Test ERP');

        $this->post('/pay/'.$payment->reference, $this->customer())
            ->assertRedirect('https://pg.eps.com.bd/PG?data=eps-txn-1');

        $payment->refresh();
        $attempt = $payment->attempts()->first();
        $this->assertSame('processing', $payment->status);
        $this->assertSame('Rahim Uddin', $payment->customer_name);

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'InitializeEPS')
            && $r->hasHeader('Authorization', 'Bearer tok-123')
            && $r->hasHeader('x-hash', (new EpsClient(['hash_key' => 'test-hash-key']))->hash($attempt->merchant_transaction_id))
            && $r['merchantId'] === 'merchant-1'
            && $r['storeId'] === 'store-1'
            && $r['totalAmount'] == 1000
            && $r['customerPhone'] === '01711000000'
            && str_ends_with($r['successUrl'], '/pay/return/'.$attempt->merchant_transaction_id.'/success'));

        $response = $this->get('/pay/return/'.$attempt->merchant_transaction_id.'/success');

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('bKash', $payment->payment_method);
        $this->assertSame('32617271711105', $payment->gateway_transaction_id);
        $this->assertEquals(20.00, (float) $payment->fee_amount);
        $this->assertEquals(980.00, (float) $payment->net_amount);
        $this->assertSame('delivered', $payment->webhook_status);

        // Signed redirect back to the app
        $location = $response->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith('https://erp.test/central-pay/return?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $q);
        $this->assertSame('paid', $q['status']);
        $this->assertTrue(Signature::check($this->merchant->app_secret, [$q['timestamp'], $q['reference'], $q['order_id'], $q['status']], $q['signature']));

        // Signed webhook to the app
        Http::assertSent(function (HttpRequest $r) {
            if ($r->url() !== 'https://erp.test/webhook/central-pay') {
                return false;
            }

            return $r['event'] === 'payment.paid'
                && $r['data']['status'] === 'paid'
                && Signature::check($this->merchant->app_secret, [$r->header('X-Timestamp')[0], $r->body()], $r->header('X-Signature')[0]);
        });

        // Paid: checkout page now only offers the way back.
        $this->get('/pay/'.$payment->reference)->assertOk()->assertSee('Payment complete');
        $this->post('/pay/'.$payment->reference, $this->customer())->assertRedirect('/pay/'.$payment->reference);
    }

    public function test_redirect_to_success_url_alone_does_not_mark_paid(): void
    {
        $this->epsStatus = 'Failed';
        $payment = $this->createPayment();
        $this->post('/pay/'.$payment->reference, $this->customer());
        $attempt = $payment->attempts()->first();

        $this->get('/pay/return/'.$attempt->merchant_transaction_id.'/success')
            ->assertRedirect('/pay/'.$payment->reference);

        // Not paid, but not final either: the scheduler keeps asking EPS.
        $this->assertSame('processing', $payment->fresh()->status);
        $this->assertSame('initiated', $attempt->fresh()->status);
    }

    public function test_eps_cancel_status_while_customer_is_paying_is_not_final(): void
    {
        // EPS reports a fresh, unpaid transaction as "Cancel".
        $this->epsStatus = 'Cancel';
        $payment = $this->createPayment();
        $this->post('/pay/'.$payment->reference, $this->customer());
        $attempt = $payment->attempts()->first();
        $attempt->update(['created_at' => now()->subMinutes(10)]);

        $this->get('/pay/'.$payment->reference)->assertOk();        // checkout re-checks
        $this->artisan('payments:reconcile')->assertSuccessful();   // scheduler re-checks
        $this->assertSame('processing', $payment->fresh()->status);

        // Customer then pays: the open attempt is still watched and turns paid.
        $this->epsStatus = 'Success';
        $this->artisan('payments:reconcile')->assertSuccessful();
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_eps_fail_redirect_fails_and_abandoned_attempts_are_given_up(): void
    {
        $this->epsStatus = 'Cancel';

        $failed = $this->createPayment();
        $this->post('/pay/'.$failed->reference, $this->customer());
        $this->get('/pay/return/'.$failed->attempts()->first()->merchant_transaction_id.'/fail')
            ->assertRedirect('/pay/'.$failed->reference);
        $this->assertSame('failed', $failed->fresh()->status);

        $abandoned = $this->createPayment(['order_id' => 'abandoned']);
        $this->post('/pay/'.$abandoned->reference, $this->customer());
        $abandoned->attempts()->update(['created_at' => now()->subHours(4)]);

        $this->artisan('payments:reconcile')->assertSuccessful();
        $this->assertSame('failed', $abandoned->fresh()->status);
        $this->assertStringContainsString('Cancel', $abandoned->fresh()->failure_reason);
    }

    public function test_eps_base_url_pointing_at_this_site_fails_fast_instead_of_hanging(): void
    {
        config(['payment.eps.base_url' => config('app.url')]);
        $payment = $this->createPayment();

        $this->post('/pay/'.$payment->reference, $this->customer())
            ->assertRedirect('/pay/'.$payment->reference)
            ->assertSessionHas('checkout_error');

        Http::assertNotSent(fn (HttpRequest $r) => str_contains($r->url(), '/v1/'));
        $this->assertSame('error', $payment->attempts()->first()->status);
    }

    public function test_amount_mismatch_is_never_marked_paid(): void
    {
        $this->epsAmount = '10.00';
        $payment = $this->createPayment();
        $this->post('/pay/'.$payment->reference, $this->customer());
        $attempt = $payment->attempts()->first();

        $this->get('/pay/return/'.$attempt->merchant_transaction_id.'/success');

        $payment->refresh();
        $this->assertSame('failed', $payment->status);
        $this->assertStringContainsString('Amount mismatch', $payment->failure_reason);
    }

    public function test_cancel_returns_to_the_app_with_cancelled_status(): void
    {
        $payment = $this->createPayment();

        $location = $this->post('/pay/'.$payment->reference.'/cancel')->assertRedirect()->headers->get('Location');

        $this->assertSame('cancelled', $payment->fresh()->status);
        $this->assertStringStartsWith('https://erp.test/subscription', $location);
        $this->assertStringContainsString('status=cancelled', $location);
    }

    public function test_status_api_rechecks_eps_while_processing(): void
    {
        $payment = $this->createPayment();
        $this->post('/pay/'.$payment->reference, $this->customer());

        $this->signed('GET', '/api/v1/payments/'.$payment->reference)
            ->assertOk()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.payment_method', 'bKash');
    }

    public function test_reconcile_verifies_open_attempts_expires_links_and_retries_webhooks(): void
    {
        $open = $this->createPayment();
        $this->post('/pay/'.$open->reference, $this->customer());
        $open->attempts()->update(['created_at' => now()->subMinutes(10)]);

        $stale = $this->createPayment(['order_id' => 'stale']);
        $stale->update(['expires_at' => now()->subMinute()]);

        $webhookFailed = $this->createPayment(['order_id' => 'wh']);
        $webhookFailed->update(['status' => 'paid', 'paid_at' => now(), 'webhook_status' => 'failed', 'webhook_attempts' => 1, 'webhook_next_attempt_at' => now()->subMinute()]);

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame('paid', $open->fresh()->status);
        $this->assertSame('expired', $stale->fresh()->status);
        $this->assertSame('delivered', $webhookFailed->fresh()->webhook_status);
    }

    public function test_disbursement_settles_unsettled_payments_and_cancel_releases_them(): void
    {
        $a = $this->createPayment(['order_id' => 'a']);
        $b = $this->createPayment(['order_id' => 'b']);
        $a->update(['status' => 'paid', 'paid_at' => now(), 'net_amount' => 980]);
        $b->update(['status' => 'paid', 'paid_at' => now(), 'net_amount' => 490]);
        $this->createPayment(['order_id' => 'c']); // pending: not included

        $service = app(DisbursementService::class);
        $this->assertSame(['count' => 2, 'amount' => 1470.0], $service->preview($this->merchant));

        $d = $service->create($this->merchant, ['method' => 'bank', 'transaction_reference' => 'BANK-1']);

        $this->assertEquals(1470, (float) $d->amount);
        $this->assertSame('completed', $d->status);
        $this->assertSame(0.0, $this->merchant->unsettledBalance());

        $service->cancel($d);
        $this->assertEquals(1470, $this->merchant->unsettledBalance());
        $this->assertSame('cancelled', $d->fresh()->status);
    }

    public function test_admin_payment_pages_load(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $payment = $this->createPayment();
        $this->post('/pay/'.$payment->reference, $this->customer());

        $this->actingAs($admin);

        foreach (['/admin', '/admin/payments', '/admin/payments/'.$payment->reference, '/admin/merchant-apps', '/admin/disbursements', '/admin/payment-integration-guide'] as $url) {
            $this->get($url)->assertOk();
        }

        $payment->update(['status' => 'paid', 'paid_at' => now(), 'net_amount' => 980]);

        Livewire::test(ManageDisbursements::class)
            ->callAction('create', ['merchant_app_id' => $this->merchant->id, 'method' => 'bank', 'status' => 'completed'])
            ->assertHasNoActionErrors();

        $this->assertSame(1, Disbursement::count());
        $this->assertNotNull($payment->fresh()->disbursement_id);
    }

    // ── helpers ────────────────────────────────────────────────────────

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'order_id' => 'SUB-5-10-1',
            'amount' => 1000,
            'currency' => 'BDT',
            'description' => 'Monthly package',
            'return_url' => 'https://erp.test/central-pay/return',
            'cancel_url' => 'https://erp.test/subscription',
            'customer' => ['name' => 'Rahim', 'email' => 'rahim@example.com'],
            'metadata' => ['business_id' => 5, 'package_id' => 10],
        ], $overrides);
    }

    protected function customer(): array
    {
        return [
            'name' => 'Rahim Uddin', 'email' => 'rahim@example.com', 'phone' => '01711000000',
            'address' => 'House 1, Road 2', 'city' => 'Dhaka', 'state' => 'Dhaka', 'postcode' => '1207', 'country' => 'BD',
        ];
    }

    protected function createPayment(array $overrides = []): Payment
    {
        $data = $this->signed('POST', '/api/v1/payments', $this->payload($overrides))->assertSuccessful()->json('data');

        return Payment::where('reference', $data['reference'])->firstOrFail();
    }

    protected function signed(string $method, string $uri, array $payload = [], ?string $secret = null, ?int $timestamp = null, ?MerchantApp $app = null): TestResponse
    {
        $app ??= $this->merchant;
        $body = $method === 'GET' ? '' : json_encode($payload);
        $timestamp ??= time();
        $signature = Signature::make($secret ?? $app->app_secret, [$timestamp, $method, parse_url($uri, PHP_URL_PATH), $body]);

        return $this->call($method, $uri, [], [], [], [
            'HTTP_X_APP_KEY' => $app->app_key,
            'HTTP_X_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $body);
    }
}
