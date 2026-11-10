<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A payout of collected money to an app's owner. Creating one settles every
 * paid, not-yet-settled payment of that app (see DisbursementService).
 */
class Disbursement extends Model
{
    public const METHODS = [
        'bank' => 'Bank transfer',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
        'cash' => 'Cash',
        'internal' => 'Internal (own account)',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'disbursed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Disbursement $d): void {
            $d->reference ??= 'DSB'.now()->format('ymd').Str::upper(Str::random(6));
        });
    }

    public function merchantApp(): BelongsTo
    {
        return $this->belongsTo(MerchantApp::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
