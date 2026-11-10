<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Payment extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',        // created by the app, customer has not gone to EPS yet
        'processing' => 'Processing',  // customer sent to EPS, result not known yet
        'paid' => 'Paid',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ];

    public const STATUS_COLORS = [
        'pending' => 'gray',
        'processing' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
        'cancelled' => 'danger',
        'expired' => 'gray',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'webhook_last_attempt_at' => 'datetime',
            'webhook_next_attempt_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $payment->reference ??= self::newReference();
        });
    }

    public static function newReference(): string
    {
        do {
            $ref = 'PAY'.now()->format('ymd').Str::upper(Str::random(8));
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function merchantApp(): BelongsTo
    {
        return $this->belongsTo(MerchantApp::class);
    }

    public function disbursement(): BelongsTo
    {
        return $this->belongsTo(Disbursement::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class)->latest('id');
    }

    public function latestAttempt(): HasOne
    {
        return $this->hasOne(PaymentAttempt::class)->latestOfMany();
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /** Paid and not yet included in a disbursement. */
    public function scopeUnsettled(Builder $query): Builder
    {
        return $query->paid()->whereNull('disbursement_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * The checkout link no longer accepts new attempts. An attempt already
     * running at EPS is still verified and can still turn the payment "paid".
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || (! $this->isPaid() && $this->expires_at && $this->expires_at->isPast());
    }

    /** The customer can (re)start paying on the checkout page. */
    public function canBePaid(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'failed', 'cancelled'], true) && ! $this->isExpired();
    }

    public function checkoutUrl(): string
    {
        return route('checkout.show', $this);
    }

    /** The shape apps receive from the API and in webhooks. */
    public function toApiArray(): array
    {
        return [
            'reference' => $this->reference,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'currency' => $this->currency,
            'fee_amount' => number_format((float) $this->fee_amount, 2, '.', ''),
            'net_amount' => number_format((float) $this->net_amount, 2, '.', ''),
            'description' => $this->description,
            'payment_method' => $this->payment_method,
            'gateway' => $this->gateway,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'merchant_transaction_id' => $this->merchant_transaction_id,
            'failure_reason' => $this->failure_reason,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
                'address' => $this->customer_address,
                'city' => $this->customer_city,
                'state' => $this->customer_state,
                'postcode' => $this->customer_postcode,
                'country' => $this->customer_country,
            ],
            'metadata' => $this->metadata ?: (object) [],
            'checkout_url' => $this->checkoutUrl(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
