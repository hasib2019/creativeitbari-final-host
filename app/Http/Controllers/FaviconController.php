<?php

namespace App\Http\Controllers;

use App\Support\Favicon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/** /favicon.ico and /site.webmanifest, built from the favicon set in Site Settings. */
class FaviconController extends Controller
{
    /** Browsers and crawlers ask for /favicon.ico whatever the page says. */
    public function ico(): SymfonyResponse
    {
        $set = setting('favicon_set');
        $path = is_array($set) && ! empty($set['dir']) ? $set['dir'].'/favicon.ico' : null;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return new Response('', 204);
        }

        return Storage::disk('public')->response($path, 'favicon.ico', [
            'Content-Type' => 'image/x-icon',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /** Lets Android "Add to home screen" use the brand icon and colours. */
    public function manifest(): JsonResponse
    {
        $name = (string) setting('site_name', config('app.name'));

        $icons = array_values(array_filter([
            ($src = Favicon::url('icon-192.png')) ? ['src' => $src, 'sizes' => '192x192', 'type' => 'image/png'] : null,
            ($src = Favicon::url('icon-512.png')) ? ['src' => $src, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'] : null,
        ]));

        return response()->json([
            'name' => $name,
            'short_name' => mb_substr($name, 0, 12),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#0a0e19',
            'theme_color' => '#0a0e19',
            'icons' => $icons,
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'public, max-age=86400'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
