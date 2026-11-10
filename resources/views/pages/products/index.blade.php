@php
    $meta = \App\Models\Product::TYPE_META;
    $heading = $type ? $meta[$type]['label'] : 'Software Products';
    $sub = $type ? $meta[$type]['description'].'. Browse, compare plans and order online.' : 'SaaS subscriptions, one-time licensed software, e-commerce solutions and web applications — ready to launch for your business.';
@endphp
<x-layouts.app :title="$heading" :description="$sub" :noindex="$search !== ''">
    <x-page-hero :title="$heading" :subtitle="$sub" eyebrow="Products" :breadcrumbs="$type ? ['Products' => route('products.index'), $heading => null] : ['Products' => null]" />

    <section class="section pt-10 sm:pt-12">
        <div class="container-x">
            {{-- Filters --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Product types">
                    <a href="{{ route('products.index', array_filter(['category' => $categorySlug])) }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition', 'bg-brand-600 text-white shadow-lg shadow-brand-600/25' => ! $type, 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $type])>All</a>
                    @foreach ($meta as $key => $m)
                        <a href="{{ route('products.index', array_filter(['type' => $key, 'category' => $categorySlug])) }}" @class(['flex shrink-0 items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold transition', 'bg-brand-600 text-white shadow-lg shadow-brand-600/25' => $type === $key, 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $type !== $key])>
                            <x-icon :name="$m['icon']" class="size-4" /> {{ $m['label'] }}
                        </a>
                    @endforeach
                </nav>
                <form method="GET" class="flex gap-2" role="search">
                    @if ($type)<input type="hidden" name="type" value="{{ $type }}">@endif
                    <select name="category" class="input w-auto py-2.5" onchange="this.form.submit()" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->slug }}" @selected($categorySlug === $c->slug)>{{ $c->name }} ({{ $c->products_count }})</option>
                        @endforeach
                    </select>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search products…" class="input w-full py-2.5 lg:w-60" aria-label="Search products">
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="card mt-12 p-12 text-center">
                    <x-icon name="heroicon-o-magnifying-glass" class="mx-auto size-12 text-slate-400" />
                    <h2 class="mt-4 text-xl font-bold">No products found</h2>
                    <p class="mt-2 text-slate-500">Try another filter, or tell us what you need — we build custom software too.</p>
                    <a href="{{ route('quote') }}" class="btn-primary mt-6">Request custom software</a>
                </div>
            @else
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-cards.product :product="$product" />
                    @endforeach
                </div>
                <div class="mt-12">{{ $products->links() }}</div>
            @endif
        </div>
    </section>

    <x-cta title="Need something custom?" subtitle="Can't find the exact software you need? We'll build it for you — on time and on budget." button-text="Get a Free Quote" />
</x-layouts.app>
