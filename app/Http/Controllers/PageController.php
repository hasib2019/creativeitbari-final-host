<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Faq;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'team' => TeamMember::active()->ordered()->get(),
            'testimonials' => Testimonial::active()->ordered()->take(6)->get(),
            'clients' => Client::active()->ordered()->get(),
        ]);
    }

    public function faq(): View
    {
        $faqs = Faq::active()->ordered()->get();

        return view('pages.faq', [
            'faqs' => $faqs,
            'groups' => $faqs->groupBy(fn ($f) => $f->category ?: 'General'),
        ]);
    }

    public function show(Page $page): View
    {
        abort_unless($page->is_active, 404);

        return view('pages.page', ['page' => $page]);
    }
}
