<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    public const STATUS_COLORS = [
        'initiated' => 'warning',
        'success' => 'success',
        'failed' => 'danger',
        'cancelled' => 'danger',
        'error' => 'danger',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'init_response' => 'array',
            'verify_response' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** EPS wants a unique, at least 10 digit transaction id per attempt. */
    public static function newMerchantTransactionId(): string
    {
        do {
            $id = now()->format('ymdHis').random_int(100000, 999999);
        } while (self::where('merchant_transaction_id', $id)->exists());

        return $id;
    }
}
