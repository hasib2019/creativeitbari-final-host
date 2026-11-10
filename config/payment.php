<?php

/*
|--------------------------------------------------------------------------
| Central payment hub
|--------------------------------------------------------------------------
|
| This site collects payments for other apps (the ERP, client sites ...)
| through EPS. Each app is a "Merchant App" in Admin → Payments with its own
| key and secret. See docs/payment-gateway-integration.md.
|
*/

return [

    // Shown on the checkout page and receipts ("CreativeItBari Pay").
    'brand' => env('PAYMENT_BRAND'),

    // Only BDT is supported by EPS.
    'currency' => 'BDT',

    // A checkout link stops working this many minutes after it is created.
    'checkout_expires_minutes' => (int) env('PAYMENT_CHECKOUT_EXPIRES', 120),

    // API requests must carry an X-Timestamp within this many seconds of now.
    'signature_tolerance' => (int) env('PAYMENT_SIGNATURE_TOLERANCE', 300),

    // Failed webhooks are retried after these delays (minutes), then given up.
    'webhook_backoff' => [1, 5, 15, 60, 180, 720],

    'eps' => [
        'sandbox' => (bool) env('EPS_SANDBOX', true),
        // Sandbox URL is from the EPS guide. For live, EPS sends the production
        // API URL with the live credentials: put it in EPS_BASE_URL.
        'sandbox_url' => 'https://sandboxpgapi.eps.com.bd',
        'base_url' => env('EPS_BASE_URL'),
        'merchant_id' => env('EPS_MERCHANT_ID'),
        'store_id' => env('EPS_STORE_ID'),
        'username' => env('EPS_USERNAME'),
        'password' => env('EPS_PASSWORD'),
        'hash_key' => env('EPS_HASH_KEY'),
        // 1 = Web, 2 = Android, 3 = iOS (EPS guide v0.4)
        'transaction_type_id' => (int) env('EPS_TRANSACTION_TYPE_ID', 1),
        // Keep below PHP's max_execution_time (30s) so a slow EPS gives a clean error.
        'timeout' => (int) env('EPS_TIMEOUT', 20),
    ],

];
