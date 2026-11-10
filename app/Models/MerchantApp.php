<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * An app (ERP, client website ...) that takes payments through this hub.
 * It authenticates API calls with app_key + an HMAC signature made from app_secret.
 */
class MerchantApp extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['app_secret'];

    protected function casts(): array
    {
        return [
            'app_secret' => 'encrypted',
            'allowed_hosts' => 'array',
            'fee_percent' => 'decimal:2',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MerchantApp $app): void {
            $app->app_key ??= self::newKey();
            $app->app_secret ??= self::newSecret();
        });
    }

    public static function newKey(): string
    {
        return 'app_'.Str::lower(Str::random(24));
    }

    public static function newSecret(): string
    {
        return 'sk_'.Str::random(48);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Whether a return/cancel/webhook URL may be used by this app. An empty
     * allow-list accepts any http(s) URL.
     */
    public function allowsUrl(?string $url): bool
    {
        if (blank($url)) {
            return true;
        }

        $parts = parse_url($url);
        if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            return false;
        }

        $hosts = collect($this->allowed_hosts ?? [])->map(fn ($h) => Str::lower(trim((string) $h)))->filter();
        if ($hosts->isEmpty()) {
            return true;
        }

        $host = Str::lower($parts['host']);
        $hostWithPort = isset($parts['port']) ? $host.':'.$parts['port'] : $host;

        return $hosts->contains(fn (string $allowed) => $allowed === $hostWithPort
            || $allowed === $host
            || (str_starts_with($allowed, '*.') && str_ends_with($host, substr($allowed, 1))));
    }

    /** Net money collected for this app that has not been paid out yet. */
    public function unsettledBalance(): float
    {
        return (float) $this->payments()->unsettled()->sum('net_amount');
    }
}
