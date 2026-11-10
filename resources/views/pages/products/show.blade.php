@php
    $images = collect([$product->thumbnail])->merge($product->gallery ?? [])->filter()->values();
    $plans = collect($product->plans ?? []);
    $periodLabel = fn ($p) => match ($p) { 'monthly' => '/month', 'yearly' => '/year', 'lifetime' => 'lifetime', 'one_time' => 'one-time', default => '' };
    $offers = $plans->isNotEmpty()
        ? $plans->map(fn ($p) => ['@type' => 'Offer', 'name' => $p['name'] ?? '', 'price' => (string) ($p['price'] ?? 0), 'priceCurrency' => setting('currency_code', 'BDT'), 'availability' => 'https://schema.org/InStock', 'url' => $product->url()])->values()->all()
        : ($product->effectivePrice() !== null ? [['@type' => 'Offer', 'price' => (string) $product->effectivePrice(), 'priceCurrency' => setting('currency_code', 'BDT'), 'availability' => 'https://schema.org/InStock', 'url' => $product->url()]] : null);
    $schema = [[
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => $product->name,
        'description' => $product->short_description ?: $product->tagline,
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'image' => $images->map(fn ($i) => media_url($i))->all() ?: null,
        'url' => $product->url(),
        'offers' => $offers,
        'publisher' => ['@id' => url('/').'#organization'],
    ]];
@endphp
<x-layouts.app :title="$product->meta_title ?: $product->name" :description="$product->meta_description ?: ($product->short_description ?: $product->tagline)" :image="$product->thumbnail" type="product" :schema="$schema">

    <section class="relative overflow-hidden border-b border-slate-200 dark:border-white/5">
        <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top_left,black,transparent_70%)]"></div>
        <div class="container-x relative grid gap-12 py-12 lg:grid-cols-2 lg:py-16">
            <div class="order-2 lg:order-1">
                <nav aria-label="Breadcrumb" class="text-sm text-slate-500 dark:text-slate-400">
                    <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a> /
                    <a href="{{ route('products.index') }}" class="hover:text-brand-600">Products</a> /
                    <a href="{{ route('products.index', ['type' => $product->type]) }}" class="hover:text-brand-600">{{ $product->typeLabel() }}</a>
                </nav>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="badge-brand"><x-icon :name="\App\Models\Product::TYPE_META[$product->type]['icon'] ?? null" class="size-3.5" /> {{ $product->typeLabel() }}</span>
                    @if ($product->category)
                        <span class="badge bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-300">{{ $product->category->name }}</span>
                    @endif
                </div>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-5xl">{{ $product->name }}</h1>
                @if ($product->tagline)
                    <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">{{ $product->tagline }}</p>
                @endif

                <div class="mt-6 flex items-end gap-3">
                    @if ($product->effectivePrice() !== null)
                        <div>
                            <span class="block text-sm text-slate-500">{{ $product->price_label ?: 'Price' }}</span>
                            <span class="text-4xl font-extrabold text-slate-900 dark:text-white">{{ money($product->effectivePrice()) }}</span>
                            @if ($product->hasDiscount())
                                <span class="ml-2 text-lg text-slate-400 line-through">{{ money($product->price) }}</span>
                                <span class="badge ml-2 bg-rose-500 text-white">Save {{ money($product->price - $product->sale_price) }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @if ($plans->isNotEmpty())
                        <a href="#pricing" class="btn-primary px-7 py-3.5">View Plans &amp; Order <x-icon name="heroicon-m-arrow-down" class="size-4" /></a>
                    @else
                        <a href="{{ route('order', ['product' => $product->slug]) }}" class="btn-primary px-7 py-3.5">Order Now <x-icon name="heroicon-m-shopping-cart" class="size-4" /></a>
                    @endif
                    @if ($product->demo_url)
                        <a href="{{ $product->demo_url }}" target="_blank" rel="noopener" class="btn-outline px-7 py-3.5"><x-icon name="heroicon-o-play-circle" class="size-5" /> Live Demo</a>
                    @endif
                </div>

                @if ($product->features)
                    <ul class="mt-8 grid gap-2.5 sm:grid-cols-2">
                        @foreach (array_slice($product->features, 0, 6) as $feature)
                            <li class="flex gap-2 text-sm"><x-icon name="heroicon-s-check-circle" class="size-5 shrink-0 text-emerald-500" /> {{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Gallery --}}
            <div class="order-1 lg:order-2" x-data="{ active: 0 }">
                <div class="card overflow-hidden p-2">
                    <div class="aspect-[16/10] overflow-hidden rounded-xl bg-gradient-to-br from-brand-100 to-violet-100 dark:from-brand-950 dark:to-slate-900">
                        @forelse ($images as $i => $img)
                            <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif src="{{ media_url($img) }}" alt="{{ $product->name }} screenshot {{ $i + 1 }}" class="size-full object-cover" width="960" height="600" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                        @empty
                            <div class="grid size-full place-items-center"><x-icon :name="\App\Models\Product::TYPE_META[$product->type]['icon'] ?? null" class="size-24 text-brand-400/70" /></div>
                        @endforelse
                    </div>
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-3 overflow-x-auto pb-1">
                        @foreach ($images as $i => $img)
                            <button type="button" @click="active = {{ $i }}" :class="active === {{ $i }} ? 'ring-2 ring-brand-500' : 'opacity-70 hover:opacity-100'" class="size-20 shrink-0 overflow-hidden rounded-lg transition" aria-label="Show image {{ $i + 1 }}">
                                <img src="{{ media_url($img) }}" alt="" class="size-full object-cover" loading="lazy" width="80" height="80">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Pricing plans --}}
    @if ($plans->isNotEmpty())
        <section id="pricing" class="section section-alt scroll-mt-24">
            <div class="container-x">
                <x-section-heading eyebrow="Pricing" title="Choose your <span class='gradient-text'>plan</span>" subtitle="All plans include installation, setup with your branding and free support." />
                <div @class(['mx-auto mt-12 grid gap-8', 'max-w-5xl lg:grid-cols-3' => $plans->count() >= 3, 'max-w-3xl md:grid-cols-2' => $plans->count() === 2, 'max-w-md' => $plans->count() === 1])>
                    @foreach ($plans as $plan)
                        @php $popular = ! empty($plan['is_popular']); @endphp
                        <div @class(['relative flex flex-col rounded-3xl border p-7', 'border-brand-500 bg-gradient-to-b from-brand-600 to-violet-700 text-white shadow-2xl shadow-brand-600/30' => $popular, 'border-slate-200 bg-white dark:border-white/10 dark:bg-ink-900/60' => ! $popular])>
                            @if ($popular)
                                <span class="badge absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-amber-950 shadow">Most Popular</span>
                            @endif
                            <h3 @class(['text-lg font-bold', 'text-white' => $popular])>{{ $plan['name'] ?? '' }}</h3>
                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold">{{ money($plan['price'] ?? 0) }}</span>
                                <span @class(['text-sm', 'text-indigo-100' => $popular, 'text-slate-500' => ! $popular])>{{ $periodLabel($plan['period'] ?? null) }}</span>
                            </div>
                            <ul class="mt-6 space-y-3 text-sm">
                                @foreach ($plan['features'] ?? [] as $f)
                                    <li class="flex gap-2.5"><x-icon name="heroicon-s-check-circle" @class(['size-5 shrink-0', 'text-cyan-300' => $popular, 'text-emerald-500' => ! $popular]) /> {{ $f }}</li>
                                @endforeach
                            </ul>
                            <div class="mt-auto pt-8">
                                <a href="{{ route('order', ['product' => $product->slug, 'plan' => $plan['name'] ?? '']) }}" @class(['btn w-full', 'bg-white text-brand-700 hover:bg-indigo-50' => $popular, 'btn-primary' => ! $popular])>Order {{ $plan['name'] ?? '' }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Description + sidebar --}}
    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-3">
            <article class="prose prose-slate max-w-none lg:col-span-2 dark:prose-invert prose-headings:font-extrabold prose-a:text-brand-600 prose-img:rounded-2xl">
                {!! $product->description !!}
                @if ($product->video_url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $product->video_url, $m))
                    <div class="not-prose mt-8 aspect-video overflow-hidden rounded-2xl">
                        <iframe src="https://www.youtube-nocookie.com/embed/{{ $m[1] }}" title="{{ $product->name }} video" class="size-full" loading="lazy" allowfullscreen allow="accelerometer; encrypted-media; picture-in-picture"></iframe>
                    </div>
                @endif
            </article>
            <aside class="space-y-6">
                @if ($product->features)
                    <div class="card p-6">
                        <h2 class="text-lg font-bold">Key features</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($product->features as $feature)
                                <li class="flex gap-2"><x-icon name="heroicon-s-check-circle" class="size-5 shrink-0 text-emerald-500" /> {{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($product->technologies)
                    <div class="card p-6">
                        <h2 class="text-lg font-bold">Built with</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($product->technologies as $tech)
                                <span class="badge bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-300">{{ $tech }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="card bg-gradient-to-br from-brand-600 to-violet-700 p-6 text-white">
                    <h2 class="text-lg font-bold text-white">Have questions?</h2>
                    <p class="mt-2 text-sm text-indigo-100">Get a free demo and consultation from our product team.</p>
                    <a href="{{ route('quote', ['service' => $product->name]) }}" class="btn mt-5 w-full bg-white text-brand-700 hover:bg-indigo-50">Request a Demo</a>
                </div>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x">
                <x-section-heading title="Related products" />
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $p)
                        <x-cards.product :product="$p" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
