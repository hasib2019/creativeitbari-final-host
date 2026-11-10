<?php

namespace App\Http\Middleware;

use App\Models\MerchantApp;
use App\Services\Payments\Signature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API auth for apps. Every request carries:
 *
 *   X-App-Key:    app_xxx
 *   X-Timestamp:  unix seconds (must be within config('payment.signature_tolerance'))
 *   X-Signature:  hex HMAC-SHA256(app_secret, timestamp \n METHOD \n /url/path \n raw body)
 */
class AuthenticateMerchantApp
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) $request->header('X-App-Key');
        $timestamp = (string) $request->header('X-Timestamp');
        $signature = (string) $request->header('X-Signature');

        if ($key === '' || $timestamp === '' || $signature === '') {
            return $this->deny('Missing X-App-Key, X-Timestamp or X-Signature header.');
        }

        if (! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > config('payment.signature_tolerance', 300)) {
            return $this->deny('X-Timestamp is missing or too far from the server time. Check the clock of your server.');
        }

        $app = MerchantApp::where('app_key', $key)->first();

        if (! $app || ! $app->is_active) {
            return $this->deny('Unknown or disabled app key.');
        }

        $body = (string) $request->getContent();
        $method = strtoupper($request->method());
        $paths = array_unique(array_filter([
            '/'.ltrim($request->path(), '/'),                       // /api/v1/payments
            parse_url($request->getRequestUri(), PHP_URL_PATH) ?: null, // /subfolder/api/v1/payments
        ]));

        $valid = collect($paths)->contains(fn (string $path) => Signature::check($app->app_secret, [$timestamp, $method, $path, $body], $signature));

        if (! $valid) {
            return $this->deny('Invalid signature.');
        }

        if (! $app->last_used_at || $app->last_used_at->lt(now()->subMinutes(5))) {
            $app->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $request->attributes->set('merchantApp', $app);

        return $next($request);
    }

    protected function deny(string $message): Response
    {
        return response()->json(['message' => $message], 401);
    }
}
