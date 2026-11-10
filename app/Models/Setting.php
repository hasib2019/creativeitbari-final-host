<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'site_settings';

    protected $fillable = ['key', 'value'];

    /**
     * All settings as a key => value array (JSON values decoded), cached forever
     * and flushed whenever a setting is saved.
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')
                ->map(function ($value) {
                    $trimmed = is_string($value) ? ltrim($value) : '';

                    if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                        $decoded = json_decode($value, true);

                        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
                    }

                    return $value;
                })
                ->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::allCached()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value],
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
