<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DomainPrice;
use App\Models\Faq;
use App\Models\HostingPlan;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'services' => Service::active()->orderByDesc('is_featured')->ordered()->take(6)->get(),
            'products' => Product::active()->with('category')->orderByDesc('is_featured')->ordered()->take(8)->get(),
            'hostingPlans' => HostingPlan::active()->where('type', 'shared')->ordered()->take(3)->get(),
            'domains' => DomainPrice::active()->orderByDesc('is_popular')->ordered()->take(6)->get(),
            'portfolios' => Portfolio::active()->orderByDesc('is_featured')->ordered()->take(6)->get(),
            'testimonials' => Testimonial::active()->ordered()->take(9)->get(),
            'posts' => Post::published()->with('category')->latest('published_at')->take(3)->get(),
            'clients' => Client::active()->ordered()->get(),
            'faqs' => Faq::active()->ordered()->take(5)->get(),
        ]);
    }
}
