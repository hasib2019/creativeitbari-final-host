<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantApp;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    /** POST /api/v1/payments — create a checkout. */
    public function store(Request $request): JsonResponse
    {
        $app = $this->app($request);

        $data = $request->validate([
            'order_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'currency' => ['nullable', 'string', 'in:BDT'],
            'description' => ['required', 'string', 'max:255'],
            'return_url' => ['required', 'url:http,https', 'max:2000'],
            'cancel_url' => ['nullable', 'url:http,https', 'max:2000'],
            'webhook_url' => ['nullable', 'url:http,https', 'max:2000'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['nullable', 'string', 'max:150'],
            'customer.email' => ['nullable', 'email', 'max:150'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'customer.city' => ['nullable', 'string', 'max:100'],
            'customer.state' => ['nullable', 'string', 'max:100'],
            'customer.postcode' => ['nullable', 'string', 'max:20'],
            'customer.country' => ['nullable', 'string', 'max:60'],
            'metadata' => ['nullable', 'array', 'max:50'],
        ]);

        if (isset($data['metadata']) && strlen(json_encode($data['metadata'])) > 5000) {
            throw ValidationException::withMessages(['metadata' => 'metadata must be under 5 KB as JSON.']);
        }

        foreach (['return_url', 'cancel_url', 'webhook_url'] as $field) {
            if (! $app->allowsUrl($data[$field] ?? null)) {
                throw ValidationException::withMessages([$field => "The {$field} host is not in this app's allowed hosts."]);
            }
        }

        // Same order_id again: idempotent replay when the amount matches.
        if ($existing = $app->payments()->where('order_id', $data['order_id'])->first()) {
            if (abs((float) $existing->amount - round((float) $data['amount'], 2)) < 0.001) {
                return response()->json(['data' => $existing->toApiArray()], 200);
            }

            return response()->json([
                'message' => 'order_id was already used with a different amount. Use a new order_id.',
                'data' => $existing->toApiArray(),
            ], 409);
        }

        $payment = $this->payments->create($app, $data, $request->ip());

        return response()->json(['data' => $payment->toApiArray()], 201);
    }

    /** GET /api/v1/payments/{reference} — current status (re-checked with EPS while processing). */
    public function show(Request $request, string $reference): JsonResponse
    {
        $payment = $this->find($request, $reference);

        if ($payment->status === 'processing') {
            $payment = $this->payments->refresh($payment);
        }

        return response()->json(['data' => $payment->toApiArray()]);
    }

    /** GET /api/v1/orders/{order_id} — same, looked up by the app's own order id. */
    public function showByOrder(Request $request, string $orderId): JsonResponse
    {
        $payment = $this->app($request)->payments()->where('order_id', $orderId)->firstOrFail();

        return $this->show($request, $payment->reference);
    }

    protected function find(Request $request, string $reference): Payment
    {
        return $this->app($request)->payments()->where('reference', $reference)->firstOrFail();
    }

    protected function app(Request $request): MerchantApp
    {
        return $request->attributes->get('merchantApp');
    }
}
