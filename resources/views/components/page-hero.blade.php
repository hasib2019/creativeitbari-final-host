@props(['title', 'subtitle' => null, 'breadcrumbs' => [], 'eyebrow' => null])
@php
    $crumbs = array_merge(['Home' => route('home')], $breadcrumbs);
    $position = 0;
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($crumbs)->map(function ($url, $label) use (&$position) {
            return array_filter(['@type' => 'ListItem', 'position' => ++$position, 'name' => $label, 'item' => $url ?: null]);
        })->values()->all(),
    ];
@endphp
<section class="relative overflow-hidden border-b border-slate-200 bg-slate-50 dark:border-white/5 dark:bg-ink-900/40">
    <div class="bg-grid pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
    <div class="pointer-events-none absolute -top-24 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-brand-500/20 blur-3xl dark:bg-brand-500/15"></div>
    <div class="container-x relative py-14 text-center sm:py-20">
        <nav aria-label="Breadcrumb" class="mb-5 flex justify-center">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                @foreach ($crumbs as $label => $url)
                    <li class="flex items-center gap-1.5">
                        @if (! $loop->first)
                            <x-icon name="heroicon-m-chevron-right" class="size-4 opacity-60" />
                        @endif
                        @if ($url && ! $loop->last)
                            <a href="{{ $url }}" class="hover:text-brand-600 dark:hover:text-brand-300">{{ $label }}</a>
                        @else
                            <span class="font-medium text-slate-700 dark:text-slate-200" aria-current="page">{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
        @if ($eyebrow)
            <span class="badge-brand mb-4">{{ $eyebrow }}</span>
        @endif
        <h1 class="mx-auto max-w-4xl text-3xl font-extrabold tracking-tight sm:text-5xl">{!! $title !!}</h1>
        @if ($subtitle)
            <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg dark:text-slate-400">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
</section>
