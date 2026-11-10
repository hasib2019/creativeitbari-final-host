<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        return view('pages.portfolio.index', [
            'items' => Portfolio::active()
                ->when($category, fn ($q) => $q->where('category', $category))
                ->orderByDesc('is_featured')->ordered()
                ->paginate(12)->withQueryString(),
            'categories' => Portfolio::active()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'category' => $category,
        ]);
    }

    public function show(Portfolio $portfolio): View
    {
        abort_unless($portfolio->is_active, 404);

        return view('pages.portfolio.show', [
            'item' => $portfolio,
            'related' => Portfolio::active()->whereKeyNot($portfolio->id)
                ->orderByRaw('category = ? desc', [$portfolio->category])
                ->ordered()->take(3)->get(),
        ]);
    }
}
