@php
    $gallery = collect($item->gallery ?? [])->filter();
    $schema = [[
        '@context' => 'https://schema.org', '@type' => 'CreativeWork',
        'name' => $item->title, 'description' => $item->short_description, 'url' => $item->url(),
        'image' => media_url($item->thumbnail), 'creator' => ['@id' => url('/').'#organization'],
        'dateCreated' => $item->completed_at?->toDateString(),
    ]];
@endphp
<x-layouts.app :title="$item->meta_title ?: $item->title" :description="$item->meta_description ?: $item->short_description" :image="$item->thumbnail" :schema="$schema">
    <x-page-hero :title="e($item->title)" :subtitle="$item->short_description" :eyebrow="$item->category" :breadcrumbs="['Portfolio' => route('portfolio.index'), $item->title => null]" />

    <section class="section">
        <div class="container-x">
            @if ($item->thumbnail)
                <img src="{{ media_url($item->thumbnail) }}" alt="{{ $item->title }}" class="w-full rounded-3xl border border-slate-200 object-cover dark:border-white/10" width="1280" height="720" fetchpriority="high">
            @endif

            <div class="mt-12 grid gap-12 lg:grid-cols-3">
                <article class="prose prose-slate max-w-none lg:col-span-2 dark:prose-invert prose-headings:font-extrabold prose-a:text-brand-600 prose-img:rounded-2xl">
                    {!! $item->content !!}
                </article>
                <aside class="card h-fit space-y-5 p-6 lg:sticky lg:top-28">
                    @foreach (array_filter(['Client' => $item->client_name, 'Category' => $item->category, 'Completed' => $item->completed_at?->format('F Y')]) as $label => $value)
                        <div><p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ $label }}</p><p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $value }}</p></div>
                    @endforeach
                    @if ($item->technologies)
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Technologies</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($item->technologies as $tech)
                                    <span class="badge bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-300">{{ $tech }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($item->project_url)
                        <a href="{{ $item->project_url }}" target="_blank" rel="noopener" class="btn-primary w-full">Visit project <x-icon name="heroicon-m-arrow-up-right" class="size-4" /></a>
                    @endif
                </aside>
            </div>

            @if ($gallery->isNotEmpty())
                {{-- Full-page screenshots are tall: each sits in a browser frame and scrolls inside it. --}}
                <div @class(['mt-14 grid gap-6', 'lg:grid-cols-2' => $gallery->count() > 1])>
                    @foreach ($gallery as $img)
                        <figure class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5 dark:border-white/10 dark:bg-ink-900" data-reveal>
                            <div class="flex items-center gap-1.5 border-b border-slate-200 px-4 py-3 dark:border-white/10">
                                <span class="size-3 rounded-full bg-accent-500"></span><span class="size-3 rounded-full bg-amber-400"></span><span class="size-3 rounded-full bg-emerald-400"></span>
                                <span class="ml-3 flex-1 truncate rounded-md bg-slate-100 px-3 py-1 font-mono text-xs text-slate-500 dark:bg-white/5 dark:text-slate-400">{{ $item->project_url ? parse_url($item->project_url, PHP_URL_HOST) : $item->title }}</span>
                                <span class="hidden font-mono text-[11px] text-slate-400 sm:inline">scroll ↓</span>
                            </div>
                            <div class="max-h-[38rem] overflow-y-auto">
                                <img src="{{ media_url($img) }}" alt="{{ $item->title }} — full page" class="block w-full" loading="lazy" width="1000" height="1805">
                            </div>
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x">
                <x-section-heading title="More projects" />
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $r)
                        <x-cards.portfolio :item="$r" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta title="Want results like this?" />
</x-layouts.app>
