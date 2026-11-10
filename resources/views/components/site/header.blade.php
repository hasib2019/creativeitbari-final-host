@php
    $productTypes = \App\Models\Product::TYPE_META;
    $phone = setting('phone');
    $email = setting('email');
    $socialLinks = array_filter([
        'Facebook' => setting('social_facebook'),
        'YouTube' => setting('social_youtube'),
        'LinkedIn' => setting('social_linkedin'),
        'Instagram' => setting('social_instagram'),
    ]);
    $is = fn (string ...$patterns) => request()->routeIs(...$patterns);
@endphp

{{-- Top bar (desktop) --}}
<div class="hidden border-b border-slate-200 bg-slate-900 text-xs text-slate-300 md:block dark:border-white/5 dark:bg-black/40">
    <div class="container-x flex h-9 items-center justify-between">
        <div class="flex items-center gap-5">
            @if ($phone)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="flex items-center gap-1.5 hover:text-white"><x-icon name="heroicon-o-phone" class="size-3.5" /> {{ $phone }}</a>
            @endif
            @if ($email)
                <a href="mailto:{{ $email }}" class="flex items-center gap-1.5 hover:text-white"><x-icon name="heroicon-o-envelope" class="size-3.5" /> {{ $email }}</a>
            @endif
        </div>
        <div class="flex items-center gap-4">
            @if ($hours = setting('working_hours'))
                <span class="flex items-center gap-1.5"><x-icon name="heroicon-o-clock" class="size-3.5" /> {{ $hours }}</span>
            @endif
            @foreach ($socialLinks as $label => $url)
                <a href="{{ $url }}" target="_blank" rel="noopener" class="hover:text-white">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</div>

<header x-data="{ open: false, scrolled: false }" x-init="scrolled = window.scrollY > 8"
        @scroll.window="scrolled = window.scrollY > 8" @keydown.escape.window="open = false"
        class="sticky top-0 z-50 transition-all duration-300"
        :class="scrolled ? 'bg-white/85 shadow-sm backdrop-blur-xl dark:bg-ink-950/80 dark:shadow-black/30' : 'bg-white dark:bg-ink-950'">
    <div class="container-x flex h-16 items-center justify-between gap-4 lg:h-20">
        <x-logo />

        {{-- Desktop navigation --}}
        <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Main navigation">
            <a href="{{ route('home') }}" @class(['nav-link', 'nav-link-active' => $is('home')])>Home</a>

            {{-- Products mega menu --}}
            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative">
                <button type="button" @click="show = !show" :aria-expanded="show" @class(['nav-link', 'nav-link-active' => $is('products.*')])>
                    Products <x-icon name="heroicon-m-chevron-down" class="size-4 transition" ::class="show && 'rotate-180'" />
                </button>
                <div x-cloak x-show="show" x-transition.origin.top class="absolute top-full left-1/2 w-[34rem] -translate-x-1/2 pt-3">
                    <div class="card grid grid-cols-2 gap-1 p-3 shadow-2xl">
                        @foreach ($productTypes as $key => $meta)
                            <a href="{{ route('products.index', ['type' => $key]) }}" class="group flex gap-3 rounded-xl p-3 transition hover:bg-slate-50 dark:hover:bg-white/5">
                                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-500/10 dark:text-brand-300">
                                    <x-icon :name="$meta['icon']" class="size-5" />
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $meta['label'] }}</span>
                                    <span class="mt-0.5 block text-xs leading-snug text-slate-500 dark:text-slate-400">{{ $meta['description'] }}</span>
                                </span>
                            </a>
                        @endforeach
                        <a href="{{ route('products.index') }}" class="col-span-2 mt-1 flex items-center justify-center gap-1 rounded-xl bg-slate-50 py-2.5 text-sm font-semibold text-brand-600 hover:bg-brand-50 dark:bg-white/5 dark:text-brand-300">
                            Browse all products <x-icon name="heroicon-m-arrow-right" class="size-4" />
                        </a>
                    </div>
                </div>
            </div>

            {{-- Services dropdown --}}
            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative">
                <button type="button" @click="show = !show" :aria-expanded="show" @class(['nav-link', 'nav-link-active' => $is('services.*')])>
                    Services <x-icon name="heroicon-m-chevron-down" class="size-4 transition" ::class="show && 'rotate-180'" />
                </button>
                <div x-cloak x-show="show" x-transition.origin.top class="absolute top-full left-1/2 w-72 -translate-x-1/2 pt-3">
                    <div class="card p-2 shadow-2xl">
                        @foreach ($navServices as $service)
                            <a href="{{ $service->url() }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-brand-600 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                                <x-icon :name="$service->icon" class="size-5 text-brand-500" /> {{ $service->title }}
                            </a>
                        @endforeach
                        <a href="{{ route('services.index') }}" class="mt-1 flex items-center justify-center gap-1 rounded-lg bg-slate-50 py-2 text-sm font-semibold text-brand-600 dark:bg-white/5 dark:text-brand-300">All services <x-icon name="heroicon-m-arrow-right" class="size-4" /></a>
                    </div>
                </div>
            </div>

            {{-- Hosting & Domain --}}
            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative">
                <button type="button" @click="show = !show" :aria-expanded="show" @class(['nav-link', 'nav-link-active' => $is('hosting', 'domains.*')])>
                    Hosting &amp; Domain <x-icon name="heroicon-m-chevron-down" class="size-4 transition" ::class="show && 'rotate-180'" />
                </button>
                <div x-cloak x-show="show" x-transition.origin.top class="absolute top-full left-1/2 w-72 -translate-x-1/2 pt-3">
                    <div class="card p-2 shadow-2xl">
                        <a href="{{ route('hosting') }}" class="flex gap-3 rounded-lg p-3 hover:bg-slate-50 dark:hover:bg-white/5">
                            <x-icon name="heroicon-o-server-stack" class="size-5 text-brand-500" />
                            <span><span class="block text-sm font-semibold text-slate-900 dark:text-white">Web Hosting</span><span class="text-xs text-slate-500 dark:text-slate-400">Fast SSD hosting with free SSL</span></span>
                        </a>
                        <a href="{{ route('domains.index') }}" class="flex gap-3 rounded-lg p-3 hover:bg-slate-50 dark:hover:bg-white/5">
                            <x-icon name="heroicon-o-globe-alt" class="size-5 text-brand-500" />
                            <span><span class="block text-sm font-semibold text-slate-900 dark:text-white">Domain Registration</span><span class="text-xs text-slate-500 dark:text-slate-400">.com, .net, .com.bd &amp; more</span></span>
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ route('portfolio.index') }}" @class(['nav-link', 'nav-link-active' => $is('portfolio.*')])>Portfolio</a>
            <a href="{{ route('blog.index') }}" @class(['nav-link', 'nav-link-active' => $is('blog.*')])>Blog</a>

            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative">
                <button type="button" @click="show = !show" :aria-expanded="show" @class(['nav-link', 'nav-link-active' => $is('about', 'faq', 'contact')])>
                    Company <x-icon name="heroicon-m-chevron-down" class="size-4 transition" ::class="show && 'rotate-180'" />
                </button>
                <div x-cloak x-show="show" x-transition.origin.top class="absolute top-full right-0 w-52 pt-3">
                    <div class="card p-2 shadow-2xl">
                        @foreach (['About Us' => route('about'), 'FAQ' => route('faq'), 'Contact' => route('contact')] as $label => $url)
                            <a href="{{ $url }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-brand-600 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex items-center gap-2">
            <x-theme-toggle />
            <a href="{{ route('quote') }}" class="btn-primary hidden py-2.5 sm:inline-flex">Get a Quote</a>
            <button type="button" @click="open = true" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-700 lg:hidden dark:border-white/10 dark:text-slate-200" aria-label="Open menu" :aria-expanded="open">
                <x-icon name="heroicon-o-bars-3" class="size-6" />
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-cloak x-show="open" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
        <div x-show="open" x-transition:enter="transition duration-300" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-200" x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 flex w-[85%] max-w-sm flex-col overflow-y-auto bg-white shadow-2xl dark:bg-ink-950">
            <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4 dark:border-white/10">
                <x-logo />
                <button type="button" @click="open = false" class="grid size-10 place-items-center rounded-xl text-slate-600 dark:text-slate-300" aria-label="Close menu">
                    <x-icon name="heroicon-o-x-mark" class="size-6" />
                </button>
            </div>
            <nav class="flex-1 space-y-1 p-4 text-base font-medium" x-data="{ sub: null }">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5">Home</a>
                @php
                    $mobileGroups = [
                        'Products' => collect($productTypes)->mapWithKeys(fn ($m, $k) => [$m['label'] => route('products.index', ['type' => $k])])->put('All products', route('products.index'))->all(),
                        'Services' => $navServices->mapWithKeys(fn ($s) => [$s->title => $s->url()])->put('All services', route('services.index'))->all(),
                        'Hosting & Domain' => ['Web Hosting' => route('hosting'), 'Domain Registration' => route('domains.index')],
                        'Company' => ['About Us' => route('about'), 'FAQ' => route('faq'), 'Contact' => route('contact')],
                    ];
                @endphp
                @foreach ($mobileGroups as $group => $links)
                    <div>
                        <button type="button" @click="sub = sub === '{{ $group }}' ? null : '{{ $group }}'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5" :aria-expanded="sub === '{{ $group }}'">
                            {{ $group }} <x-icon name="heroicon-m-chevron-down" class="size-5 transition" ::class="sub === '{{ $group }}' && 'rotate-180'" />
                        </button>
                        <div x-show="sub === '{{ $group }}'" x-collapse.duration.200ms class="ml-3 space-y-0.5 border-l border-slate-200 pl-3 dark:border-white/10">
                            @foreach ($links as $label => $url)
                                <a href="{{ $url }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:text-brand-600 dark:text-slate-400 dark:hover:text-white">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <a href="{{ route('portfolio.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5">Portfolio</a>
                <a href="{{ route('blog.index') }}" class="block rounded-lg px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5">Blog</a>
            </nav>
            <div class="space-y-3 border-t border-slate-200 p-4 dark:border-white/10">
                <a href="{{ route('quote') }}" class="btn-primary w-full">Get a Quote</a>
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="btn-outline w-full"><x-icon name="heroicon-o-phone" class="size-4" /> {{ $phone }}</a>
                @endif
            </div>
        </div>
    </div>
</header>
