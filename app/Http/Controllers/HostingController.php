<?php

namespace App\Http\Controllers;

use App\Models\DomainPrice;
use App\Models\Faq;
use App\Models\HostingPlan;
use Illuminate\View\View;

class HostingController extends Controller
{
    public function __invoke(): View
    {
        $plans = HostingPlan::active()->ordered()->get();

        return view('pages.hosting', [
            'plansByType' => $plans->groupBy('type'),
            'domains' => DomainPrice::active()->orderByDesc('is_popular')->ordered()->take(6)->get(),
            'faqs' => Faq::active()->where('category', 'Hosting')->ordered()->get(),
        ]);
    }
}
