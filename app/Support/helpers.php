<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /** Read a site setting managed from Admin → Site Settings. */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default; // e.g. before migrations have run
        }
    }
}

if (! function_exists('money')) {
    /** Format a price with the currency symbol set in Site Settings. */
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $amount = (float) $amount;
        $formatted = number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);

        return $withSymbol ? setting('currency_symbol', '৳').$formatted : $formatted;
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL of an uploaded file. Accepts storage paths ("products/a.jpg"),
     * absolute URLs, or null (returns $fallback).
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
