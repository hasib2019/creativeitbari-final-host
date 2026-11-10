@php
    $schema = [[
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $post->excerptText(),
        'image' => media_url($post->thumbnail),
        'datePublished' => ($post->published_at ?? $post->created_at)->toAtomString(),
        'dateModified' => $post->updated_at->toAtomString(),
        'author' => ['@type' => 'Organization', 'name' => setting('site_name', config('app.name')), 'url' => url('/')],
        'publisher' => ['@id' => url('/').'#organization'],
        'mainEntityOfPage' => $post->url(),
        'keywords' => $post->tags ? implode(', ', $post->tags) : null,
        'articleSection' => $post->category?->name,
    ]];
    $shareUrl = urlencode($post->url());
    $shareTitle = urlencode($post->title);
@endphp
<x-layouts.app :title="$post->meta_title ?: $post->title" :description="$post->meta_description ?: $post->excerptText()" :image="$post->thumbnail" type="article" :schema="$schema">
    @push('head')
        <meta property="article:published_time" content="{{ ($post->published_at ?? $post->created_at)->toAtomString() }}">
        @if ($post->category)<meta property="article:section" content="{{ $post->category->name }}">@endif
    @endpush

    <x-page-hero :title="e($post->title)" :breadcrumbs="array_filter(['Blog' => route('blog.index'), $post->category?->name => $post->category ? route('blog.category', $post->category) : null]) + [\Illuminate\Support\Str::limit($post->title, 40) => null]">
        <div class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
            @if ($post->category)
                <a href="{{ route('blog.category', $post->category) }}" class="badge-brand">{{ $post->category->name }}</a>
            @endif
            <span class="flex items-center gap-1.5"><x-icon name="heroicon-o-calendar" class="size-4" /> <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('F d, Y') }}</time></span>
            <span class="flex items-center gap-1.5"><x-icon name="heroicon-o-clock" class="size-4" /> {{ $post->readingTime() }} min read</span>
            <span class="flex items-center gap-1.5"><x-icon name="heroicon-o-eye" class="size-4" /> {{ number_format($post->views) }} views</span>
        </div>
    </x-page-hero>

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @if ($post->thumbnail)
                    <img src="{{ media_url($post->thumbnail) }}" alt="{{ $post->title }}" class="mb-10 w-full rounded-3xl object-cover" width="960" height="540" fetchpriority="high">
                @endif
                <article class="prose prose-slate prose-lg max-w-none dark:prose-invert prose-headings:font-extrabold prose-a:text-brand-600 prose-img:rounded-2xl">
                    {!! $post->content !!}
                </article>

                <div class="mt-10 flex flex-col gap-5 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                    @if ($post->tags)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('blog.index', ['q' => $tag]) }}" class="badge bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:bg-white/5 dark:text-slate-300">#{{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold">Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="btn-ghost py-2">Facebook</a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="btn-ghost py-2">LinkedIn</a>
                        <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="btn-ghost py-2">WhatsApp</a>
                    </div>
                </div>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <div class="card p-6">
                    <h2 class="text-lg font-bold">Categories</h2>
                    <ul class="mt-3 space-y-1">
                        @foreach ($categories as $c)
                            <li><a href="{{ route('blog.category', $c) }}" class="flex justify-between rounded-lg px-2 py-2 text-sm hover:bg-slate-50 hover:text-brand-600 dark:hover:bg-white/5">{{ $c->name }} <span class="text-slate-400">{{ $c->posts_count }}</span></a></li>
                        @endforeach
                    </ul>
                </div>
                @if ($recent->isNotEmpty())
                    <div class="card p-6">
                        <h2 class="text-lg font-bold">Recent posts</h2>
                        <ul class="mt-4 space-y-4">
                            @foreach ($recent as $r)
                                <li><a href="{{ $r->url() }}" class="text-sm leading-snug font-semibold text-slate-800 hover:text-brand-600 dark:text-slate-200">{{ $r->title }}</a><p class="mt-1 text-xs text-slate-500">{{ $r->published_at?->format('M d, Y') }}</p></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card bg-gradient-to-br from-brand-600 to-violet-700 p-6 text-white">
                    <h2 class="text-lg font-bold text-white">Need help with your project?</h2>
                    <p class="mt-2 text-sm text-indigo-100">Free consultation with our experts.</p>
                    <a href="{{ route('quote') }}" class="btn mt-5 w-full bg-white text-brand-700 hover:bg-indigo-50">Get a Quote</a>
                </div>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x">
                <x-section-heading title="Related articles" />
                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @foreach ($related as $r)
                        <x-cards.post :post="$r" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
