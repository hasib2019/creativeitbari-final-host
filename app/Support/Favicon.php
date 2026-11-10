<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Turns the one favicon uploaded in Site Settings into every size browsers
 * and phones ask for, and writes the <link> tags for them.
 *
 *   favicon.ico            16 + 32 + 48 px (PNG inside an ICO, for /favicon.ico and old browsers)
 *   favicon-16/32.png      browser tabs
 *   apple-touch-icon.png   180 px, iPhone / iPad home screen
 *   icon-192/512.png       Android home screen and the web app manifest
 *
 * Files go to settings/favicon/{hash}/ on the public disk; the hash of the
 * source is part of the path, so a new upload is never served from a stale cache.
 */
class Favicon
{
    public const SIZES = [
        'favicon-16.png' => 16,
        'favicon-32.png' => 32,
        'apple-touch-icon.png' => 180,
        'icon-192.png' => 192,
        'icon-512.png' => 512,
    ];

    private const ICO_SIZES = [16, 32, 48];

    /**
     * Build every size from the uploaded source (a public-disk path).
     *
     * @return array{hash: string, dir: string}
     */
    public static function generate(string $source): array
    {
        $disk = Storage::disk('public');
        $bytes = $disk->get($source);

        if ($bytes === null || ! function_exists('imagecreatefromstring')) {
            throw new RuntimeException('The favicon could not be read (or GD is not installed).');
        }

        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            throw new RuntimeException('The favicon is not an image PHP can read. Upload a PNG, JPG or WebP.');
        }

        $square = self::square($image);
        $hash = substr(sha1($bytes), 0, 12);
        $dir = 'settings/favicon/'.$hash;

        foreach (self::SIZES as $name => $size) {
            $disk->put($dir.'/'.$name, self::png($square, $size));
        }

        $disk->put($dir.'/favicon.ico', self::ico(array_map(fn (int $size) => [$size, self::png($square, $size)], self::ICO_SIZES)));

        // Older sets are useless once the new one exists.
        foreach ($disk->directories('settings/favicon') as $old) {
            if ($old !== $dir) {
                $disk->deleteDirectory($old);
            }
        }

        return ['hash' => $hash, 'dir' => $dir];
    }

    /** Public URL of one generated file, or null when no set exists for the current favicon. */
    public static function url(string $file): ?string
    {
        $set = setting('favicon_set');

        if (! is_array($set) || empty($set['dir']) || ($set['source'] ?? null) !== setting('favicon')) {
            return null;
        }

        return media_url($set['dir'].'/'.$file);
    }

    /** The <link> tags for the page head. */
    public static function tags(): string
    {
        $source = setting('favicon');

        if (! $source) {
            return '';
        }

        if (! self::url('favicon-32.png')) {
            // Uploaded before sizes were generated: the original still works as an icon.
            return '<link rel="icon" href="'.e(media_url($source)).'">';
        }

        return implode("\n    ", [
            '<link rel="icon" href="'.e(self::url('favicon.ico')).'" sizes="48x48">',
            '<link rel="icon" type="image/png" sizes="32x32" href="'.e(self::url('favicon-32.png')).'">',
            '<link rel="icon" type="image/png" sizes="16x16" href="'.e(self::url('favicon-16.png')).'">',
            '<link rel="apple-touch-icon" sizes="180x180" href="'.e(self::url('apple-touch-icon.png')).'">',
            '<link rel="manifest" href="'.e(route('manifest')).'">',
        ]);
    }

    /** Centre-crop to a square, so a slightly wide or tall upload is never squashed. */
    private static function square(\GdImage $image): \GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);

        if ($w === $h) {
            return $image;
        }

        $side = min($w, $h);
        $square = self::canvas($side);
        imagecopy($square, $image, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), $side, $side);

        return $square;
    }

    private static function png(\GdImage $square, int $size): string
    {
        $canvas = self::canvas($size);
        imagecopyresampled($canvas, $square, 0, 0, 0, 0, $size, $size, imagesx($square), imagesy($square));

        ob_start();
        imagepng($canvas, null, 9);

        return (string) ob_get_clean();
    }

    /** A transparent canvas that keeps the alpha channel. */
    private static function canvas(int $size): \GdImage
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        return $canvas;
    }

    /**
     * An ICO file holding PNG images (supported by every browser since IE Vista).
     *
     * @param  list<array{0: int, 1: string}>  $images  [size, png bytes]
     */
    private static function ico(array $images): string
    {
        $header = pack('vvv', 0, 1, count($images));
        $offset = 6 + 16 * count($images);
        $entries = '';
        $data = '';

        foreach ($images as [$size, $png]) {
            // Width/height bytes: 0 means 256.
            $entries .= pack('CCCCvvVV', $size % 256, $size % 256, 0, 0, 1, 32, strlen($png), $offset);
            $data .= $png;
            $offset += strlen($png);
        }

        return $header.$entries.$data;
    }
}
