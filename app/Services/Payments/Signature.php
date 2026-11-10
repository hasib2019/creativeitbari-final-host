<?php

namespace App\Services\Payments;

/**
 * HMAC-SHA256 signatures shared with the apps (see docs/payment-gateway-integration.md):
 *
 *   signature = hex( HMAC_SHA256( app_secret, part1 + "\n" + part2 + "\n" + … ) )
 *
 *   API request  parts: timestamp, HTTP method, URL path, raw body
 *   Webhook      parts: timestamp, raw body
 *   Return URL   parts: timestamp, reference, order_id, status
 */
class Signature
{
    /** @param  array<int, string|int>  $parts */
    public static function make(string $secret, array $parts): string
    {
        return hash_hmac('sha256', implode("\n", array_map('strval', $parts)), $secret);
    }

    /** @param  array<int, string|int>  $parts */
    public static function check(string $secret, array $parts, ?string $signature): bool
    {
        return is_string($signature) && $signature !== ''
            && hash_equals(self::make($secret, $parts), strtolower($signature));
    }
}
