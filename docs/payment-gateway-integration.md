# CreativeItBari Pay: Payment Gateway Integration Guide

**Version 1.0 · November 2026**

CreativeItBari Pay is our **central payment hub**. It runs on the main website (`{{CENTRAL_URL}}`) and is the only system that talks to **EPS (Easy Payment System)**. Our other apps (the ERP, client websites, SaaS products) never call EPS themselves. They send the customer to the hub, and the hub sends the customer back once EPS has confirmed the payment.

All the money goes into a single EPS merchant account. Admin → **Payments** lists every payment from every app. Admin → **Disbursements** records the money paid out to each app's owner.

> In this guide, `{{CENTRAL_URL}}` stands for the central website's address: `http://127.0.0.1:8000` locally, `https://creativeitbari.com` in production. The copy of this guide inside the admin panel fills it in for you.

---

## 1. How a payment works

```
 Your app (server)            Customer's browser              Payment hub                    EPS
 ─────────────────            ──────────────────              ───────────                    ───
 1. POST /api/v1/payments ─────────────────────────────────▶  creates payment (pending)
    ◀──────────────────── { reference, checkout_url } ───────
 2. redirect ─────────────▶  opens checkout_url ───────────▶  billing form (name, phone…)
                             submits form ──────────────────▶  InitializeEPS ───────────────▶
                             ◀──────────────────────────────────────── EPS payment page ◀────
                             pays with bKash/Nagad/card ─────────────────────────────────────▶
                             ◀── EPS redirects back ─────────  verifies with EPS (CheckMerchantTransactionStatus)
 3.                           ◀───────────────────────────────  POST webhook (signed) to your app
 4. GET return_url?reference=…&status=paid&signature=…  ◀───  redirects the customer to your app
 5. GET /api/v1/payments/{reference} ──────────────────────▶  { status: "paid", amount … }
 6. activate the order / subscription (exactly once)
```

Your app always learns the result in **two independent ways**: the **webhook** (step 3) and the **return redirect** (step 4). Handle both, and make the handling idempotent. Either one may arrive first, and either one may never arrive at all (the customer closes the tab, or your server is briefly down).

---

## 2. Integration checklist

- [ ] An admin creates your app in **Admin → Payments → Connected Apps → Connect an app**:
  - **Webhook URL**: e.g. `https://yourapp.com/webhook/central-pay`
  - **Allowed hosts**: e.g. `yourapp.com`. The hub rejects any return, cancel or webhook URL on another host.
- [ ] Copy the 3 values from **API keys** into your app's `.env`:
  ```dotenv
  CENTRAL_PAY_URL={{CENTRAL_URL}}
  CENTRAL_PAY_APP_KEY=app_xxxxxxxxxxxxxxxxxxxxxxxx
  CENTRAL_PAY_APP_SECRET=sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
  ```
- [ ] Create a local `payments` table (section 8.2) **before** you call the hub.
- [ ] Create checkouts with **signed** API calls (sections 3 and 4).
- [ ] Add a **return route** that checks the signature and then calls the status API (section 5).
- [ ] Add a **webhook route**: CSRF-exempt, signature checked, idempotent, answers 2xx (section 6).
- [ ] Fulfil the order **only** when the status is `paid` **and** the amount matches.
- [ ] Test the paid, failed and cancelled cases (section 10).

---

## 3. Signing requests

Every API request is authenticated with an HMAC-SHA256 signature made from your **app secret**. The secret itself is never sent.

| Header | Value |
|---|---|
| `X-App-Key` | your app key (`app_…`) |
| `X-Timestamp` | current Unix time in seconds. It must be within **5 minutes** of the hub's clock, so keep your server clock in sync (NTP). |
| `X-Signature` | `hex( HMAC_SHA256( app_secret, string_to_sign ) )`, lowercase hex |
| `Content-Type` | `application/json` (POST) |
| `Accept` | `application/json` |

```
string_to_sign = timestamp + "\n" + METHOD + "\n" + path + "\n" + body
```

- `METHOD`: `POST` or `GET`, in upper case.
- `path`: the URL path only, without host or query string, e.g. `/api/v1/payments` or `/api/v1/payments/PAY261110AB12CD34`.
- `body`: the **exact** raw JSON bytes you send. Use an empty string for GET. Encode the JSON once and send that same string; don't let the HTTP client re-encode it.

**PHP**

```php
$body      = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$timestamp = (string) time();
$path      = '/api/v1/payments';
$signature = hash_hmac('sha256', "$timestamp\nPOST\n$path\n$body", $appSecret);
```

**Node.js**

```js
const crypto = require('crypto');
const body = JSON.stringify(payload);
const ts = Math.floor(Date.now() / 1000).toString();
const signature = crypto.createHmac('sha256', appSecret)
  .update(`${ts}\nPOST\n/api/v1/payments\n${body}`).digest('hex');
```

The hub answers `401 {"message": "…"}` when a header is missing, the key is unknown or disabled, the timestamp is too old, or the signature does not match.

---

## 4. API reference

Base URL: `{{CENTRAL_URL}}/api/v1`. All requests and responses are JSON. Successful responses wrap the payment in `data`.

### 4.1 Create a checkout: `POST /api/v1/payments`

| Field | Type | Required | Notes |
|---|---|---|---|
| `order_id` | string ≤ 100 | **yes** | Your own unique id for this attempt to pay. Unique per app. Allowed characters: `A-Z a-z 0-9 . _ : / -`. Use a new one for every new checkout, e.g. `SUB-{business}-{package}-{time}`. |
| `amount` | number | **yes** | In BDT, at least 1, at most 2 decimals. **Your server computes it.** Never take it from the browser. |
| `currency` | string | no | Only `BDT`. |
| `description` | string ≤ 255 | **yes** | Shown to the customer and sent to EPS as the product name, e.g. "Pro Plan, 1 month". |
| `return_url` | URL | **yes** | Where the customer comes back after paying (section 5). |
| `cancel_url` | URL | no | Where the customer comes back after cancelling. Defaults to `return_url`. |
| `webhook_url` | URL | no | Overrides the app's default webhook URL for this payment. |
| `customer` | object | no | `name, email, phone, address, city, state, postcode, country`. These pre-fill the checkout form. EPS needs them all, so the customer fills in whatever is missing. |
| `metadata` | object | no | Up to 50 keys and 5 KB. Returned unchanged in every response and webhook. |

```http
POST /api/v1/payments
X-App-Key: app_ajdp3wwba0iwdtk6lrksybvj
X-Timestamp: 1794330309
X-Signature: 6f1c…
Content-Type: application/json

{
  "order_id": "SUB-1-10-261110223500-HIYE",
  "amount": 1000,
  "currency": "BDT",
  "description": "Monthly Fee (1 months), Dhaka Eye Wear Ltd",
  "return_url": "https://erp.example.com/central-pay/return",
  "cancel_url": "https://erp.example.com/central-pay/return",
  "webhook_url": "https://erp.example.com/webhook/central-pay",
  "customer": { "name": "Hasib Uzzaman", "email": "owner@example.com", "phone": "01711000000", "city": "Dhaka" },
  "metadata": { "business_id": 1, "package_id": 10 }
}
```

**201 Created**: send the customer to `data.checkout_url`.

```json
{
  "data": {
    "reference": "PAY261110RIKVY6PM",
    "order_id": "SUB-1-10-261110223500-HIYE",
    "status": "pending",
    "amount": "1000.00",
    "currency": "BDT",
    "fee_amount": "0.00",
    "net_amount": "0.00",
    "description": "Monthly Fee (1 months), Dhaka Eye Wear Ltd",
    "payment_method": null,
    "gateway": "eps",
    "gateway_transaction_id": null,
    "merchant_transaction_id": null,
    "failure_reason": null,
    "customer": { "name": "Hasib Uzzaman", "email": "owner@example.com", "phone": "01711000000", "address": null, "city": "Dhaka", "state": null, "postcode": null, "country": null },
    "metadata": { "business_id": 1, "package_id": 10 },
    "checkout_url": "{{CENTRAL_URL}}/pay/PAY261110RIKVY6PM",
    "paid_at": null,
    "expires_at": "2026-11-10T19:05:00+00:00",
    "created_at": "2026-11-10T17:05:00+00:00"
  }
}
```

| Response | Meaning |
|---|---|
| `201` | Created. |
| `200` | The same `order_id` was already created with the **same amount**. You get the existing payment back, so a request retried after a network error is safe. |
| `409` | The `order_id` was already used with a **different amount**. Use a new `order_id`. The existing payment is in `data`. |
| `422` | Validation error, or a URL whose host is not in the app's *allowed hosts*. Details are in `errors`. |
| `401` | Signature or authentication problem (section 3). |
| `429` | More than 120 requests a minute. |

A checkout link accepts payment for **2 hours** (`expires_at`). After that, create a new checkout.

### 4.2 Get a payment: `GET /api/v1/payments/{reference}`

Returns the same `data` object. If the payment is still `processing`, the hub first asks EPS again, so this call always gives the most up-to-date answer. **This is the call that decides whether you got paid.**

### 4.3 Get a payment by your order id: `GET /api/v1/orders/{order_id}`

Same response. Useful when you lost the `reference`.

### 4.4 Payment statuses

| Status | Meaning | What your app does |
|---|---|---|
| `pending` | Created; the customer has not gone to EPS yet. | Wait. |
| `processing` | The customer is at EPS, or EPS has not confirmed yet. | Wait. Do **not** fulfil and do not cancel. The hub keeps checking every 5 minutes and gives up after 3 hours without a payment (→ `failed`). |
| `paid` | EPS confirmed that the money was received. | **Fulfil, exactly once.** |
| `failed` | The payment did not go through. The customer may retry on the same link. | Show "try again". |
| `cancelled` | The customer cancelled. They may still retry on the same link. | Show "cancelled". |
| `expired` | The link expired without a payment. | Create a new checkout if needed. |

> A payment can move from `failed` or `cancelled` to `paid` if the customer tries again. Always act on the **latest** status, and never treat `failed` as final for refunds or stock.

---

## 5. Customer return

After the payment (or a cancel), the hub redirects the customer to your `return_url` (or `cancel_url`) with these query parameters added:

```
https://yourapp.com/central-pay/return?reference=PAY…&order_id=…&status=paid&timestamp=1794330309&signature=ca5b…
```

```
signature = hex( HMAC_SHA256( app_secret, timestamp + "\n" + reference + "\n" + order_id + "\n" + status ) )
```

Your return handler must:

1. Check the signature, and reject a `timestamp` older than 1 hour.
2. Find your local row by `reference` (or `order_id`).
3. Call `GET /api/v1/payments/{reference}`. **Do not trust `status` from the URL**: it is only a hint for the message you show.
4. Run the same idempotent "apply status" logic as the webhook (section 7).
5. Show a message: paid → "Payment received", processing → "Being confirmed", otherwise → "Not completed".

The return route should **not** require a login. The customer's session may have expired while they were paying. Process the payment first, then redirect to a page that does require login.

---

## 6. Webhooks

On every status change (`paid`, `failed`, `cancelled`, `expired`) the hub sends `POST` to your webhook URL:

```http
POST /webhook/central-pay
Content-Type: application/json
X-Event: payment.paid
X-App-Key: app_ajdp3wwba0iwdtk6lrksybvj
X-Timestamp: 1794330309
X-Signature: 9a41…

{ "event": "payment.paid", "sent_at": "2026-11-10T17:05:09+00:00", "data": { …payment object, same as the API… } }
```

```
X-Signature = hex( HMAC_SHA256( app_secret, X-Timestamp + "\n" + raw_request_body ) )
```

Rules:

- Exclude the route from **CSRF** protection. In Laravel, add it to the `VerifyCsrfToken` / `preventRequestForgery` exceptions.
- Check the signature over the **raw** body (`$request->getContent()`) **before** you parse it. Reject a timestamp more than 10 minutes off.
- Answer with any **2xx** quickly. Anything else, or a timeout after 15 seconds, is retried after 1, 5, 15, 60, 180 and 720 minutes.
- Be **idempotent**: the same event can arrive more than once, and it may arrive before or after the return redirect.
- Your webhook handler must **not** call the hub API. The `data` is already signed, and in local development both apps run single-threaded servers that would wait for each other.
- An admin can resend a webhook from **Admin → Payments → (payment) → Resend webhook**.

---

## 7. The "apply status" rule (most important)

Write **one** function, and call it from both the return handler and the webhook:

```
lock the local payment row (SELECT … FOR UPDATE)
if remote.order_id != local.order_id            → stop (not ours)
if remote.status == "paid":
    if remote.amount != local.amount            → mark "amount_mismatch", alert, stop
    if local.fulfilled_at is empty:
        fulfil the order (activate the subscription, …)
        local.fulfilled_at = now
    local.status = "paid"
else if local.status != "paid":
    local.status = remote.status
save, commit
```

The row lock plus the "already fulfilled?" check make it safe when the webhook and the return arrive at the same moment.

---

## 8. Laravel recipe (copy and adapt)

This is how the ERP does it (section 12 lists the files). It needs Laravel 9 or newer.

### 8.1 Config: `config/centralpay.php`

```php
return [
    'url'        => rtrim((string) env('CENTRAL_PAY_URL', ''), '/'),
    'app_key'    => env('CENTRAL_PAY_APP_KEY'),
    'app_secret' => env('CENTRAL_PAY_APP_SECRET'),
    'timeout'    => 20,
];
```

### 8.2 Local table

```php
Schema::create('central_payments', function (Blueprint $table) {
    $table->id();
    $table->string('order_id', 100)->unique();      // what you send as order_id
    $table->string('reference', 40)->nullable()->unique(); // hub reference
    $table->decimal('amount', 12, 2);
    $table->string('status', 20)->default('pending');
    $table->string('payment_method')->nullable();   // bKash, Nagad, VISA …
    $table->json('response')->nullable();           // last data from the hub
    $table->timestamp('fulfilled_at')->nullable();
    // + your own columns: user_id, order/package id …
    $table->timestamps();
});
```

### 8.3 Client

```php
use Illuminate\Support\Facades\Http;

class CentralPayClient
{
    public function create(array $payload): array { return $this->send('POST', '/api/v1/payments', $payload); }

    public function get(string $reference): array { return $this->send('GET', '/api/v1/payments/'.rawurlencode($reference)); }

    public function verifyReturn(array $q): bool
    {
        foreach (['reference', 'order_id', 'status', 'timestamp', 'signature'] as $k) {
            if (empty($q[$k]) || ! is_string($q[$k])) return false;
        }
        if (abs(time() - (int) $q['timestamp']) > 3600) return false;

        return hash_equals($this->sign([$q['timestamp'], $q['reference'], $q['order_id'], $q['status']]), $q['signature']);
    }

    public function verifyWebhook(string $body, ?string $ts, ?string $sig): bool
    {
        if (! $ts || ! $sig || ! ctype_digit($ts) || abs(time() - (int) $ts) > 600) return false;

        return hash_equals($this->sign([$ts, $body]), $sig);
    }

    private function sign(array $parts): string
    {
        return hash_hmac('sha256', implode("\n", $parts), config('centralpay.app_secret'));
    }

    private function send(string $method, string $path, ?array $payload = null): array
    {
        $url  = config('centralpay.url').$path;
        $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ts   = (string) time();

        $req = Http::acceptJson()->timeout(config('centralpay.timeout'))->withHeaders([
            'X-App-Key'   => config('centralpay.app_key'),
            'X-Timestamp' => $ts,
            'X-Signature' => $this->sign([$ts, $method, parse_url($url, PHP_URL_PATH), $body]),
        ]);

        $res = $method === 'GET' ? $req->get($url) : $req->withBody($body, 'application/json')->post($url);

        if (! $res->successful() || ! isset($res['data'])) {
            throw new \RuntimeException('Payment hub error '.$res->status().': '.($res['message'] ?? $res->body()));
        }

        return $res['data'];
    }
}
```

### 8.4 Routes

```php
Route::post('/checkout/{order}/pay', [CentralPayController::class, 'start'])->middleware('auth');
Route::get('/central-pay/return', [CentralPayController::class, 'handleReturn']);   // no auth
Route::post('/webhook/central-pay', [CentralPayController::class, 'webhook']);     // no auth, no CSRF
```

### 8.5 Controller

```php
public function start(Order $order, CentralPayClient $hub)
{
    $local = CentralPayment::create([
        'order_id' => 'ORD-'.$order->id.'-'.now()->format('ymdHis').'-'.Str::upper(Str::random(4)),
        'amount'   => $order->total,                 // computed on the server
    ]);

    $data = $hub->create([
        'order_id'    => $local->order_id,
        'amount'      => (float) $local->amount,
        'description' => 'Order #'.$order->id,
        'return_url'  => route('centralpay.return'),
        'webhook_url' => url('/webhook/central-pay'),
        'customer'    => ['name' => $order->customer_name, 'email' => $order->email, 'phone' => $order->phone],
        'metadata'    => ['order' => $order->id],
    ]);

    $local->update(['reference' => $data['reference']]);

    return redirect()->away($data['checkout_url']);
}

public function handleReturn(Request $request, CentralPayClient $hub)
{
    $local = CentralPayment::where('reference', $request->query('reference'))->first();
    abort_unless($local && $hub->verifyReturn($request->query()), 403);

    $local = $this->apply($local, $hub->get($local->reference));   // ask the hub, don't trust the URL

    return redirect('/orders')->with('status', $local->status === 'paid' ? 'Payment received' : 'Payment not completed');
}

public function webhook(Request $request, CentralPayClient $hub)
{
    $body = $request->getContent();
    if (! $hub->verifyWebhook($body, $request->header('X-Timestamp'), $request->header('X-Signature'))) {
        return response()->json(['message' => 'Invalid signature'], 401);
    }

    $data  = json_decode($body, true)['data'];
    $local = CentralPayment::where('order_id', $data['order_id'])->firstOrFail();
    $this->apply($local, $data);

    return response()->json(['ok' => true]);
}

private function apply(CentralPayment $local, array $data): CentralPayment
{
    return DB::transaction(function () use ($local, $data) {
        $local = CentralPayment::whereKey($local->id)->lockForUpdate()->first();
        if ($data['order_id'] !== $local->order_id) throw new \RuntimeException('Not our payment');

        $local->fill(['reference' => $data['reference'], 'response' => $data, 'payment_method' => $data['payment_method']]);

        if ($data['status'] === 'paid') {
            if (abs((float) $data['amount'] - (float) $local->amount) > 0.009) {
                $local->status = 'amount_mismatch';                 // alert someone
            } else {
                if (! $local->fulfilled_at) {
                    // >>> fulfil exactly once: activate subscription, mark order paid …
                    $local->fulfilled_at = now();
                }
                $local->status = 'paid';
            }
        } elseif ($local->status !== 'paid') {
            $local->status = $data['status'];
        }

        $local->save();

        return $local;
    });
}
```

Exclude `webhook/*` from CSRF. Laravel 11 and later: `$middleware->validateCsrfTokens(except: ['webhook/*'])` in `bootstrap/app.php`. Laravel 10 and earlier: add `'/webhook/*'` to `$except` in `app/Http/Middleware/VerifyCsrfToken.php`.

---

## 9. Plain PHP (no framework)

```php
function hub_request(string $method, string $path, ?array $payload = null): array {
    $url  = getenv('CENTRAL_PAY_URL').$path;
    $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $ts   = (string) time();
    $sig  = hash_hmac('sha256', "$ts\n$method\n".parse_url($url, PHP_URL_PATH)."\n$body", getenv('CENTRAL_PAY_APP_SECRET'));

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_POSTFIELDS     => $method === 'GET' ? null : $body,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json', 'Accept: application/json',
            'X-App-Key: '.getenv('CENTRAL_PAY_APP_KEY'), "X-Timestamp: $ts", "X-Signature: $sig",
        ],
    ]);
    $res  = json_decode(curl_exec($ch), true);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code >= 300 || empty($res['data'])) throw new RuntimeException($res['message'] ?? "Hub error $code");

    return $res['data'];
}

// Webhook endpoint
$body = file_get_contents('php://input');
$ts   = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';
$ok   = ctype_digit($ts) && abs(time() - (int) $ts) <= 600
     && hash_equals(hash_hmac('sha256', "$ts\n$body", getenv('CENTRAL_PAY_APP_SECRET')), $_SERVER['HTTP_X_SIGNATURE'] ?? '');
if (! $ok) { http_response_code(401); exit; }
$data = json_decode($body, true)['data'];
// … apply status (section 7) …
http_response_code(200); echo '{"ok":true}';
```

---

## 10. Testing

| Case | How |
|---|---|
| **Paid** | Pay in the EPS sandbox. Your app should get the webhook plus a return with `status=paid`, and fulfil **once**. |
| **Failed** | Fail or decline in the EPS sandbox. The customer lands back on the hub checkout with "try again"; your webhook gets `payment.failed`. |
| **Cancelled** | Click "Cancel and return" on the checkout. You get a return with `status=cancelled`. |
| **Return arrives twice** | Open the same return URL again: nothing must be fulfilled a second time. |
| **Forged return / webhook** | Change one character of `signature` and expect a rejection (401 / error message). |
| **Webhook down** | Stop your app and pay. Start it again: the hub retries, or an admin presses *Resend webhook*. |

For local development, run the central site (`php artisan serve`, port 8000) and your app (e.g. port 8001) on the same machine. Both URLs are reachable from the browser and from each other.

---

## 11. Going live (hub side, admin)

1. Get the **live** EPS credentials from EPS: Merchant ID, Store ID, user name, password, Hash Key, and the live API base URL.
2. In the central site's `.env`:
   ```dotenv
   EPS_SANDBOX=false
   EPS_BASE_URL=https://<live-api-host-from-EPS>
   EPS_MERCHANT_ID=…
   EPS_STORE_ID=…
   EPS_USERNAME=…
   EPS_PASSWORD=…
   EPS_HASH_KEY=…
   ```
   Then run `php artisan config:clear` (or `optimize`).
3. Add the scheduler cron. It verifies payments left in `processing`, expires old links and retries webhooks:
   ```
   * * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
   ```
4. Use **HTTPS** everywhere, and set every app's **allowed hosts** to its real domain.
5. In each app, set `CENTRAL_PAY_URL` to the live hub URL.

---

## 12. Admin panel

| Screen | What it is for |
|---|---|
| **Dashboard → Payments** | Collected today and this month, money still to disburse, payments waiting for EPS. |
| **Payments** | Every payment, with tabs (Paid / Not disbursed / Processing / Failed), filters (app, date, settlement) and totals. The payment page shows the customer, the EPS ids, every attempt with the raw EPS responses, and webhook delivery. Actions: **Check with EPS** and **Resend webhook**. |
| **Disbursements** | *New disbursement*: choose an app (optionally a cut-off date). It takes every paid, not-yet-disbursed payment, totals the **net** amount (after the app's fee %), and marks those payments settled. *Cancel* releases them again. |
| **Connected Apps** | Create apps, see their API keys, issue a new secret, set the webhook URL, allowed hosts, fee % and payout account. |
| **Integration Guide** | This document. |

---

## 13. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `401 Invalid signature` | You signed a different body from the one you sent (the JSON was re-encoded), used the wrong path (include `/api/v1/…`), or have the wrong secret. |
| `401 X-Timestamp … too far` | Your server clock is off. Enable NTP. |
| `422 … not in this app's allowed hosts` | Add your domain (with `:port` when it is not 80/443) under Connected Apps → Allowed hosts. |
| Customer paid, app not updated | Check Admin → Payments → the payment → *App callback*: the webhook's last response tells you what your server answered. Fix it, then *Resend webhook*. |
| Payment stuck in `processing` | EPS has not confirmed yet. Use *Check with EPS*; the scheduler also checks every 5 minutes. |
| `Maximum execution time … exceeded` / checkout hangs | `EPS_BASE_URL` points to the hub itself. Leave it **empty** for the sandbox; set it only to the live EPS API URL. The hub now refuses this setting with a clear error. |
| "The payment gateway could not start this payment" | EPS rejected the request: wrong credentials, store id or hash key. With `APP_DEBUG=true` the checkout shows the EPS message; the attempt's *Initialize response* has the details. |

---

## 14. Reference implementation: the ERP (eye-care-update, UltimatePOS)

| File | Role |
|---|---|
| `config/centralpay.php` | Hub URL, key, secret, button label |
| `Modules/Superadmin/Utils/CentralPayClient.php` | Signed API client and signature checks |
| `Modules/Superadmin/Http/Controllers/CentralPayController.php` | `start` → hub, `handleReturn`, `webhook`, idempotent `applyRemote` that creates the subscription once |
| `Modules/Superadmin/Entities/CentralPayment.php` + migration `create_central_payments_table` | Local payment rows |
| `Modules/Superadmin/Http/Controllers/BaseController.php` | Adds the `centralpay` gateway when configured |
| `Modules/Superadmin/Resources/views/subscription/partials/pay_centralpay.blade.php` | The "Pay online" button on `/subscription/{id}/pay` |
| `Modules/Superadmin/Routes/web.php` | `POST /subscription/{package}/central-pay`, `GET /central-pay/return`, `POST /webhook/central-pay` |

### Second reference: smart-order-solution (Laravel 13 + Inertia/React, multi-tenant)

The shop renews its plan from **Settings → Plan & Billing**. When the plan's end date passes, every screen except billing is locked.

| File | Role |
|---|---|
| `config/centralpay.php` | Hub URL, key, secret, how many days before the end to warn |
| `app/Support/CentralPay/CentralPayClient.php` | Signed API client and signature checks |
| `app/Actions/Billing/StartPlanPayment.php` | Opens the checkout at the plan's own price; refuses a free plan or one smaller than the users/branches in use |
| `app/Actions/Billing/ApplyPlanPayment.php` | Idempotent "apply status": locks the row and appends one subscription (from the day after the current period ends, or today) |
| `app/Http/Controllers/BillingController.php`, `CentralPayWebhookController.php` | `/settings/billing`, `/billing/return`, `/webhook/central-pay` |
| `app/Http/Middleware/SetTenantContext.php` | Lets `billing.*` through for a lapsed shop and blocks everything else |

**Hub side (this website):** `app/Services/Eps/EpsClient.php` (EPS API, hash, token cache), `app/Services/Payments/*` (life cycle, webhooks, disbursements, signatures), `app/Http/Controllers/CheckoutController.php` and `Api/PaymentController.php`, `app/Console/Commands/ReconcilePayments.php`, and `config/payment.php`.

**EPS details used by the hub** (EPS Merchant API guide v0.4): `x-hash = base64(HMAC-SHA512(key = Hash Key, data = value))`. The value is the user name for GetToken and the merchantTransactionId for InitializeEPS and CheckMerchantTransactionStatus. The bearer token is cached until shortly before it expires. Each attempt gets a new merchantTransactionId of at least 10 digits, and the hub marks a payment paid only when CheckMerchantTransactionStatus returns `Status: Success` **with the expected amount**. Any other EPS status is not final on its own: EPS reports a transaction the customer is still paying on as `Cancel`. An attempt fails only when EPS sends the customer to the fail or cancel URL, or after 3 hours without `Success`.
