<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $type = array_key_exists($request->query('type'), Product::TYPES) ? $request->query('type') : null;
        $categorySlug = $request->query('category');
        $search = trim((string) $request->query('q'));

        $products = Product::active()
            ->with('category')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('tagline', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")))
            ->orderByDesc('is_featured')
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('pages.products.index', [
            'products' => $products,
            'categories' => ProductCategory::active()->ordered()->withCount(['products' => fn ($q) => $q->active()])->get(),
            'type' => $type,
            'categorySlug' => $categorySlug,
            'search' => $search,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category');

        return view('pages.products.show', [
            'product' => $product,
            'related' => Product::active()->with('category')
                ->whereKeyNot($product->id)
                ->where(fn ($q) => $q->where('type', $product->type)
                    ->when($product->product_category_id, fn ($w) => $w->orWhere('product_category_id', $product->product_category_id)))
                ->ordered()->take(3)->get(),
        ]);
    }
}
