@php
    $siteName = setting('site_name', config('app.name'));
    $socials = array_filter([
        'Facebook' => [setting('social_facebook'), 'M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.52 1.49-3.92 3.78-3.92 1.1 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33v7A10 10 0 0 0 22 12.06z'],
        'YouTube' => [setting('social_youtube'), 'M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z'],
        'LinkedIn' => [setting('social_linkedin'), 'M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z'],
        'Instagram' => [setting('social_instagram'), 'M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23a3.72 3.72 0 0 1-.9 1.38c-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.3-1.46.72-2.13 1.38A5.88 5.88 0 0 0 .63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.3.79.72 1.46 1.38 2.13a5.88 5.88 0 0 0 2.13 1.38c.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56a5.88 5.88 0 0 0 2.13-1.38 5.88 5.88 0 0 0 1.38-2.13c.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.88 5.88 0 0 0-1.38-2.13A5.88 5.88 0 0 0 19.86.63C19.1.33 18.22.13 16.95.07 15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z'],
        'X' => [setting('social_twitter'), 'M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.65l-5.21-6.82-5.97 6.82H1.68l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23zm-1.16 17.52h1.83L7.08 4.13H5.12l11.96 15.64z'],
        'GitHub' => [setting('social_github'), 'M12 .3a12 12 0 0 0-3.8 23.38c.6.12.83-.26.83-.57L9 21.07c-3.34.72-4.04-1.61-4.04-1.61-.55-1.39-1.34-1.76-1.34-1.76-1.08-.74.09-.73.09-.73 1.2.09 1.83 1.24 1.83 1.24 1.07 1.83 2.81 1.3 3.5 1 .1-.78.42-1.31.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.14-.3-.54-1.52.1-3.18 0 0 1-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.28-1.55 3.29-1.23 3.29-1.23.64 1.66.24 2.88.12 3.18a4.65 4.65 0 0 1 1.23 3.22c0 4.61-2.8 5.63-5.48 5.92.42.36.81 1.1.81 2.22l-.01 3.29c0 .31.2.69.82.57A12 12 0 0 0 12 .3'],
    ], fn ($s) => filled($s[0]));
@endphp
<footer class="relative overflow-hidden border-t border-slate-200 bg-slate-50 dark:border-white/5 dark:bg-ink-950">
    <div class="pointer-events-none absolute -bottom-40 left-1/2 h-80 w-[60rem] -translate-x-1/2 rounded-full bg-brand-600/10 blur-3xl"></div>
    <div class="container-x relative grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12 lg:py-16">
        <div class="lg:col-span-4">
            <x-logo size="lg" />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                {{ setting('footer_about', 'We build SaaS products, custom software, e-commerce stores and web applications — and keep them online with fast, secure hosting and domains.') }}
            </p>
            @if ($socials)
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($socials as $label => [$url, $path])
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}"
                           class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-brand-500 hover:bg-brand-600 hover:text-white dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $path }}"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <h3 class="text-sm font-bold tracking-wider uppercase">Products</h3>
            <ul class="mt-4 space-y-2.5 text-sm">
                @foreach (\App\Models\Product::TYPE_META as $key => $meta)
                    <li><a href="{{ route('products.index', ['type' => $key]) }}" class="text-slate-600 hover:text-brand-600 dark:text-slate-400 dark:hover:text-white">{{ $meta['label'] }}</a></li>
                @endforeach
                <li><a href="{{ route('hosting') }}" class="text-slate-600 hover:text-brand-600 dark:text-slate-400 dark:hover:text-white">Web Hosting</a></li>
                <li><a href="{{ route('domains.index') }}" class="text-slate-600 hover:text-brand-600 dark:text-slate-400 dark:hover:text-white">Domains</a></li>
            </ul>
        </div>

        <div class="lg:col-span-2">
            <h3 class="text-sm font-bold tracking-wider uppercase">Company</h3>
            <ul class="mt-4 space-y-2.5 text-sm">
                @foreach (['About Us' => route('about'), 'Services' => route('services.index'), 'Portfolio' => route('portfolio.index'), 'Blog' => route('blog.index'), 'FAQ' => route('faq'), 'Contact' => route('contact')] as $label => $url)
                    <li><a href="{{ $url }}" class="text-slate-600 hover:text-brand-600 dark:text-slate-400 dark:hover:text-white">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="sm:col-span-2 lg:col-span-4">
            <h3 class="text-sm font-bold tracking-wider uppercase">Get in touch</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                @if ($address = setting('address'))
                    <li class="flex gap-3"><x-icon name="heroicon-o-map-pin" class="mt-0.5 size-5 shrink-0 text-brand-500" /> <span>{{ $address }}</span></li>
                @endif
                @if ($phone = setting('phone'))
                    <li class="flex gap-3"><x-icon name="heroicon-o-phone" class="size-5 shrink-0 text-brand-500" /> <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hover:text-brand-600 dark:hover:text-white">{{ $phone }}</a></li>
                @endif
                @if ($email = setting('email'))
                    <li class="flex gap-3"><x-icon name="heroicon-o-envelope" class="size-5 shrink-0 text-brand-500" /> <a href="mailto:{{ $email }}" class="hover:text-brand-600 dark:hover:text-white">{{ $email }}</a></li>
                @endif
            </ul>

            <form action="{{ route('subscribe') }}" method="POST" class="mt-6">
                @csrf
                <label for="newsletter-email" class="text-sm font-semibold text-slate-900 dark:text-white">Subscribe to our newsletter</label>
                <div class="mt-2 flex gap-2">
                    <input id="newsletter-email" type="email" name="email" required placeholder="you@example.com" class="input py-2.5" autocomplete="email">
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <button class="btn-primary shrink-0 px-4 py-2.5" aria-label="Subscribe"><x-icon name="heroicon-m-paper-airplane" class="size-4" /></button>
                </div>
                @if (session('subscribed'))
                    <p class="mt-2 text-sm text-emerald-600 dark:text-emerald-400">{{ session('subscribed') }}</p>
                @endif
                {{-- $errors is absent on error pages rendered outside the web middleware --}}
                @if (isset($errors) && $errors->newsletter->has('email'))
                    <p class="mt-2 text-sm text-rose-600">{{ $errors->newsletter->first('email') }}</p>
                @endif
            </form>
        </div>
    </div>

    {{-- Accepted payment methods (EPS) --}}
    <div class="relative border-t border-slate-200 dark:border-white/5">
        <div class="container-x py-6">
            <x-payment-methods variant="strip" />
        </div>
    </div>

    <div class="relative border-t border-slate-200 dark:border-white/5">
        <div class="container-x flex flex-col items-center justify-between gap-3 py-6 text-sm text-slate-500 sm:flex-row dark:text-slate-400">
            <p>{{ setting('copyright_text', '© '.date('Y').' '.$siteName.'. All rights reserved.') }}</p>
            @if ($footerPages->isNotEmpty())
                <nav class="flex flex-wrap justify-center gap-x-5 gap-y-1" aria-label="Legal">
                    @foreach ($footerPages as $page)
                        <a href="{{ $page->url() }}" class="hover:text-brand-600 dark:hover:text-white">{{ $page->title }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
</footer>
