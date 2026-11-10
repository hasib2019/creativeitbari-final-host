@props(['product'])
<article class="card-hover group flex flex-col overflow-hidden">
    <a href="{{ $product->url() }}" class="relative block aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 to-violet-100 dark:from-brand-950 dark:to-slate-900">
        @if ($product->thumbnail)
            <img src="{{ media_url($product->thumbnail) }}" alt="{{ $product->name }}" loading="lazy" width="640" height="400" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="grid size-full place-items-center">
                <x-icon :name="\App\Models\Product::TYPE_META[$product->type]['icon'] ?? null" class="size-16 text-brand-400/70" />
            </div>
        @endif
        <span class="badge absolute top-3 left-3 bg-white/90 text-slate-800 shadow backdrop-blur dark:bg-ink-950/80 dark:text-slate-100">{{ $product->typeLabel() }}</span>
        @if ($product->hasDiscount())
            <span class="badge absolute top-3 right-3 bg-rose-500 text-white">-{{ round(100 - $product->sale_price / $product->price * 100) }}%</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        @if ($product->category)
            <p class="text-xs font-semibold tracking-wide text-brand-600 uppercase dark:text-brand-400">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-1 text-lg font-bold"><a href="{{ $product->url() }}" class="hover:text-brand-600 dark:hover:text-brand-300">{{ $product->name }}</a></h3>
        <p class="mt-2 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">{{ $product->short_description ?: $product->tagline }}</p>
        <div class="mt-auto flex items-end justify-between gap-3 pt-5">
            <div>
                @if ($product->effectivePrice() !== null)
                    <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $product->price_label ?: 'Price' }}</span>
                    <span class="text-xl font-extrabold text-slate-900 dark:text-white">{{ money($product->effectivePrice()) }}</span>
                    @if ($product->hasDiscount())
                        <span class="ml-1 text-sm text-slate-400 line-through">{{ money($product->price) }}</span>
                    @endif
                @else
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Custom pricing</span>
                @endif
            </div>
            <a href="{{ $product->url() }}" class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-500/10 dark:text-brand-300" aria-label="View {{ $product->name }}">
                <x-icon name="heroicon-m-arrow-up-right" class="size-5" />
            </a>
        </div>
    </div>
</article>
