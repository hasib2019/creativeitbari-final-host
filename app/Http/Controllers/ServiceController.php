<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services.index', [
            'services' => Service::active()->ordered()->get(),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('pages.services.show', [
            'service' => $service,
            'others' => Service::active()->whereKeyNot($service->id)->ordered()->take(3)->get(),
            'faqs' => Faq::active()->ordered()->take(4)->get(),
        ]);
    }
}
