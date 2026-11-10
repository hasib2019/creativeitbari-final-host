<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Page;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('services.index'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('hosting'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('domains.index'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('portfolio.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('blog.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('faq'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.6', 'changefreq' => 'yearly'],
            ['loc' => route('quote'), 'priority' => '0.6', 'changefreq' => 'yearly'],
        ]);

        $models = [
            [Product::active()->get(), 0.8],
            [Service::active()->get(), 0.8],
            [Portfolio::active()->get(), 0.6],
            [Post::published()->get(), 0.7],
            [Page::active()->get(), 0.3],
        ];

        foreach ($models as [$items, $priority]) {
            foreach ($items as $item) {
                $urls->push(['loc' => $item->url(), 'lastmod' => $item->updated_at?->toAtomString(), 'priority' => (string) $priority]);
            }
        }

        foreach (BlogCategory::active()->get() as $category) {
            $urls->push(['loc' => route('blog.category', $category), 'priority' => '0.5']);
        }

        return response()
            ->view('feeds.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /order', 'Disallow: /thank-you', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /']; // keep staging/local out of search engines

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
