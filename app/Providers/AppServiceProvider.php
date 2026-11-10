<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Service;
use App\Services\Eps\EpsClient;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EpsClient::class, fn () => EpsClient::make());
    }

    public function boot(): void
    {
        // Behind a proxy / on shared hosting with SSL, make sure generated URLs are https.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Uploaded files are linked on the host the visitor is actually using.
        // With APP_URL on another host (localhost vs 127.0.0.1, www vs bare
        // domain) the admin's file previews became cross-origin requests the
        // browser blocks, and every image field spun on "loading" for ever.
        if (! $this->app->runningInConsole()) {
            config(['filesystems.disks.public.url' => rtrim(request()->getSchemeAndHttpHost().request()->getBaseUrl(), '/').'/storage']);
        }

        // Navigation data for the public header & footer (memoised per request).
        View::composer(['components.site.header', 'components.site.footer'], function ($view) {
            static $nav;

            $nav ??= [
                'navServices' => Service::active()->ordered()->take(8)->get(['id', 'title', 'slug', 'icon']),
                'footerPages' => Page::active()->where('show_in_footer', true)->orderBy('id')->get(['id', 'title', 'slug']),
            ];

            $view->with($nav);
        });
    }
}
