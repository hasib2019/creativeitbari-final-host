@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'canonical' => null,
    'noindex' => false,
    'keywords' => null,
    'schema' => [],
])
@php
    $siteName = setting('site_name', config('app.name'));
    $tagline = setting('site_tagline', 'Software, Web & Hosting Solutions');
    $pageTitle = $title
        ? $title.' | '.$siteName
        : setting('seo_meta_title', $siteName.' — '.$tagline);
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($description ?: setting('seo_meta_description', $tagline)), 160, '…');
    $metaImage = media_url($image) ?: media_url(setting('seo_og_image')) ?: media_url(setting('logo'));
    $canonicalUrl = $canonical ?: url()->current().(request()->integer('page') > 1 ? '?page='.request()->integer('page') : '');

    $socials = array_values(array_filter([
        setting('social_facebook'), setting('social_youtube'), setting('social_linkedin'),
        setting('social_instagram'), setting('social_twitter'), setting('social_github'),
    ]));

    $baseSchema = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => media_url(setting('logo')),
            'email' => setting('email'),
            'telephone' => setting('phone'),
            'address' => setting('address') ? ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressCountry' => 'BD'] : null,
            'sameAs' => $socials ?: null,
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => $siteName,
            'url' => url('/'),
            'publisher' => ['@id' => url('/').'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('blog.index').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ];
    $jsonLd = collect(array_merge($baseSchema, $schema))
        ->map(fn ($item) => array_filter($item, fn ($v) => $v !== null && $v !== ''));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if ($keywords || setting('seo_meta_keywords'))
        <meta name="keywords" content="{{ $keywords ?: setting('seo_meta_keywords') }}">
    @endif
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph / Twitter --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $title ?: $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="en_US">
    @if ($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?: $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">

    @if (setting('google_site_verification'))
        <meta name="google-site-verification" content="{{ setting('google_site_verification') }}">
    @endif

    <meta name="theme-color" content="#0a0e19" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0e19" media="(prefers-color-scheme: dark)">
    <link rel="icon" href="{{ media_url(setting('favicon'), asset('favicon.ico')) }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} Blog" href="{{ route('blog.feed') }}">

    {{-- Apply saved / OS theme before first paint to avoid a light→dark flash --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
            // Scroll-reveal hides [data-reveal] until it scrolls in. If the
            // script never starts (blocked, offline), show everything anyway.
            document.documentElement.classList.add('reveal-ready');
            setTimeout(function () {
                if (!window.__revealStarted) document.documentElement.classList.remove('reveal-ready');
            }, 2500);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @foreach ($jsonLd as $item)
        <script type="application/ld+json">{!! json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    @if ($gaId = setting('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $gaId }}');</script>
    @endif
    @if ($pixelId = setting('facebook_pixel_id'))
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $pixelId }}');fbq('track','PageView');</script>
    @endif
    {!! setting('custom_head_code') !!}
    @stack('head')
</head>
<body class="min-h-screen font-sans">
    {!! setting('custom_body_code') !!}
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

    <x-site.header />

    <main id="main">
        {{ $slot }}
    </main>

    <x-site.footer />

    @if ($whatsapp = setting('whatsapp'))
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener"
           class="fixed right-4 bottom-4 z-40 grid size-14 place-items-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 transition hover:scale-110"
           aria-label="Chat on WhatsApp">
            <svg class="size-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.5h-.01a9.43 9.43 0 0 1-4.8-1.32l-.35-.2-3.57.93.95-3.48-.22-.36A9.4 9.4 0 0 1 2.6 12.04C2.6 6.84 6.84 2.6 12.05 2.6a9.4 9.4 0 0 1 9.44 9.45c0 5.21-4.24 9.45-9.44 9.45zm8.04-17.5A11.29 11.29 0 0 0 12.05.67C5.78.67.68 5.77.68 12.04c0 2 .52 3.96 1.52 5.68L.58 23.33l5.75-1.5a11.33 11.33 0 0 0 5.71 1.46h.01c6.27 0 11.37-5.1 11.37-11.37 0-3.04-1.18-5.9-3.33-8.04z"/></svg>
        </a>
    @endif

    @stack('scripts')
</body>
</html>
