@php
    $stats = setting('home_stats', [
        ['value' => '150+', 'label' => 'Projects Delivered'],
        ['value' => '50+', 'label' => 'Software Products'],
        ['value' => '99.9%', 'label' => 'Hosting Uptime'],
        ['value' => '24/7', 'label' => 'Support'],
    ]);
    $process = setting('home_process', []);
    $whyUs = setting('home_why_us', []);
    $productTypes = \App\Models\Product::TYPE_META;
    $faqSchema = $faqs->isEmpty() ? [] : [[
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($f) => [
            '@type' => 'Question',
            'name' => $f->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
        ])->all(),
    ]];
@endphp

<x-layouts.app :schema="$faqSchema">

    {{-- ===================== HERO (dark "IT" island in both themes) ===================== --}}
    @php
        $typedWords = array_values(array_filter((array) setting('hero_typed_words', [])))
            ?: [setting('hero_highlight', 'grows your business'), 'scales with you', 'automates your work', 'sells online 24/7', 'runs in the cloud'];
        $terminalLines = [
            '$ creativeitbari new your-business --stack=laravel,react',
            '✓ SaaS dashboard, POS & ERP modules ready',
            '✓ E-commerce store with bKash, Nagad & card checkout',
            '$ creativeitbari deploy --ssl --cdn',
            '→ NVMe cloud hosting · daily backups · free SSL',
            '✓ Domain connected: yourbusiness.com',
            '! Live in 24–48 hours. Support: 24/7',
        ];
        $glyphs = [
            ['</>', '8%', '16s', '0s', '0.35', 'text-brand-400 text-xl'],
            ['{ }', '18%', '19s', '4s', '0.25', 'text-accent-400 text-lg'],
            ['01', '30%', '14s', '2s', '0.3', 'text-brand-300 text-sm'],
            ['=>', '44%', '21s', '7s', '0.22', 'text-fuchsia-400 text-lg'],
            ['#', '57%', '17s', '1s', '0.28', 'text-brand-400 text-2xl'],
            ['λ', '68%', '23s', '5s', '0.25', 'text-accent-300 text-xl'],
            ['$_', '79%', '15s', '9s', '0.3', 'text-brand-300 text-base'],
            ['[ ]', '90%', '20s', '3s', '0.25', 'text-fuchsia-300 text-lg'],
            ['10', '13%', '25s', '11s', '0.2', 'text-brand-500 text-xs'],
            ['::', '63%', '18s', '13s', '0.22', 'text-accent-400 text-sm'],
        ];
    @endphp
    <section class="tech-hero">
        {{-- Background layers, back to front --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="orb top-[-10rem] left-[-8rem] size-[34rem] bg-brand-500/25"></div>
            <div class="orb top-[8rem] right-[-10rem] size-[30rem] bg-accent-600/20 [animation-delay:-6s]"></div>
            <div class="orb bottom-[-12rem] left-1/3 size-[26rem] bg-fuchsia-600/15 [animation-delay:-12s]"></div>
            <div class="bg-grid absolute inset-0 opacity-40 [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
            <div class="grid-floor"></div>
            <canvas data-network class="absolute inset-0"></canvas>
            <div class="scan-line"></div>
            @foreach ($glyphs as [$glyph, $left, $duration, $delay, $opacity, $class])
                <span class="glyph {{ $class }}" style="left: {{ $left }}; --d: {{ $duration }}; --delay: {{ $delay }}; --o: {{ $opacity }}">{{ $glyph }}</span>
            @endforeach
        </div>

        <div class="container-x relative grid items-center gap-14 pt-16 pb-12 sm:pt-20 lg:grid-cols-[1.2fr_1fr] lg:pt-24 lg:pb-16">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-400/30 bg-brand-400/10 px-3.5 py-1.5 font-mono text-xs font-semibold text-brand-300 backdrop-blur">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-accent-500"></span></span>
                    <span class="text-accent-400">&gt;</span> {{ setting('hero_badge', 'Software • Web Apps • Hosting • Domains') }}
                </span>

                <h1 class="mt-6 text-[2.1rem] leading-[1.08] font-extrabold tracking-tight text-white sm:text-5xl xl:text-[3.3rem]">
                    {{ setting('hero_title', 'We build software that') }}
                    <span class="mt-1 block min-h-[1.15em]" x-data="typer(@js($typedWords))">
                        <span class="gradient-text" x-text="text">{{ $typedWords[0] }}</span><span class="caret" aria-hidden="true"></span>
                    </span>
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-400">
                    {{ setting('hero_subtitle', 'Ready-made SaaS & one-time software, custom web applications and e-commerce stores — launched on our fast, secure hosting with your own domain.') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ setting('hero_primary_url', route('products.index')) }}" class="btn-primary px-7 py-3.5 text-base">
                        {{ setting('hero_primary_text', 'Explore Products') }} <x-icon name="heroicon-m-arrow-right" class="size-5" />
                    </a>
                    <a href="{{ setting('hero_secondary_url', route('quote')) }}" class="btn border border-brand-400/40 bg-brand-400/5 px-7 py-3.5 text-base text-white backdrop-blur hover:border-brand-300 hover:bg-brand-400/15">
                        <x-icon name="heroicon-m-code-bracket" class="size-5 text-brand-300" /> {{ setting('hero_secondary_text', 'Get a Free Quote') }}
                    </a>
                </div>

                {{-- Inline domain search --}}
                <form action="{{ route('domains.index') }}" method="GET" class="mt-10 max-w-xl" role="search">
                    <label for="hero-domain" class="font-mono text-xs font-semibold tracking-wider text-brand-300 uppercase">// Find your perfect domain</label>
                    <div class="mt-2 flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 p-1.5 shadow-lg shadow-black/30 backdrop-blur focus-within:border-brand-400/60">
                        <x-icon name="heroicon-o-globe-alt" class="ml-2 size-5 shrink-0 text-brand-400" />
                        <input id="hero-domain" name="domain" type="text" placeholder="yourbusiness.com" autocomplete="off" class="min-w-0 flex-1 border-0 bg-transparent px-1 py-2.5 font-mono text-sm text-white placeholder-slate-500 focus:ring-0 focus:outline-none">
                        <button class="btn-primary px-5 py-2.5">Search</button>
                    </div>
                    @if ($domains->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 font-mono text-sm text-slate-500">
                            @foreach ($domains->take(4) as $d)
                                <span><strong class="text-slate-200">{{ $d->tld }}</strong> {{ money($d->register_price) }}</span>
                            @endforeach
                        </div>
                    @endif
                </form>
            </div>

            {{-- Hero visual --}}
            <div class="relative mx-auto my-12 w-full max-w-xl lg:max-w-none">
                {{-- Orbit rings --}}
                <div class="pointer-events-none absolute inset-0 -z-10 grid place-items-center" aria-hidden="true">
                    <div class="animate-spin-slow size-[115%] rounded-full border border-dashed border-brand-400/20"></div>
                    <div class="animate-spin-reverse absolute size-[90%] rounded-full border border-accent-400/15">
                        <span class="absolute top-1/2 -left-1.5 size-3 rounded-full bg-accent-500 shadow-[0_0_14px_3px] shadow-accent-500/70"></span>
                    </div>
                </div>

                @if ($heroImage = media_url(setting('hero_image')))
                    <img src="{{ $heroImage }}" alt="{{ setting('site_name', config('app.name')) }}" class="relative z-10 w-full rounded-3xl" width="720" height="560" fetchpriority="high">
                @else
                    <div class="terminal z-10" x-data="terminal(@js($terminalLines))" x-intersect.once="run()">
                        <div class="flex items-center gap-1.5 border-b border-white/10 px-4 py-3">
                            <span class="size-3 rounded-full bg-accent-500"></span><span class="size-3 rounded-full bg-amber-400"></span><span class="size-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-3 flex-1 truncate font-mono text-xs text-slate-500">~/projects/your-business — zsh</span>
                            <span class="font-mono text-[10px] text-brand-400">● LIVE</span>
                        </div>
                        <div class="min-h-[19rem] space-y-1.5 p-5 font-mono text-[13px] leading-relaxed sm:text-sm">
                            <template x-for="(line, i) in shown" :key="i">
                                <p :class="tone(line)"><span x-show="line.startsWith('$ ')" class="text-accent-400">❯ </span><span x-text="line.startsWith('$ ') ? line.slice(2) : line"></span></p>
                            </template>
                            {{-- One prompt line: the command being typed, or just the caret. --}}
                            <p class="text-white"><span class="text-accent-400">❯ </span><span x-text="typing.slice(2)"></span><span class="caret"></span></p>
                            <noscript>@foreach ($terminalLines as $line)<p class="text-slate-400">{{ $line }}</p>@endforeach</noscript>
                        </div>
                        <div class="grid grid-cols-3 border-t border-white/10 text-center font-mono text-[11px]">
                            <div class="border-r border-white/10 px-2 py-2.5"><span class="block text-slate-500">uptime</span><span class="font-bold text-emerald-400">99.9%</span></div>
                            <div class="border-r border-white/10 px-2 py-2.5"><span class="block text-slate-500">response</span><span class="font-bold text-brand-300">&lt; 1 hr</span></div>
                            <div class="px-2 py-2.5"><span class="block text-slate-500">deploy</span><span class="font-bold text-accent-400">24–48h</span></div>
                        </div>
                    </div>

                    <div class="animate-float absolute -top-16 left-6 z-20 hidden items-center gap-3 rounded-2xl border border-white/10 bg-ink-800/90 p-3 pr-5 shadow-xl shadow-black/40 backdrop-blur sm:flex">
                        <span class="grid size-10 place-items-center rounded-xl bg-emerald-500/15 text-emerald-400"><x-icon name="heroicon-o-shield-check" class="size-6" /></span>
                        <span><span class="block text-xs text-slate-400">Free SSL</span><span class="text-sm font-bold text-white">Secure Hosting</span></span>
                    </div>
                    <div class="animate-float absolute -right-2 -bottom-16 z-20 hidden items-center gap-3 rounded-2xl border border-white/10 bg-ink-800/90 p-3 pr-5 shadow-xl shadow-black/40 backdrop-blur [animation-delay:1.5s] sm:flex">
                        <span class="grid size-10 place-items-center rounded-xl bg-accent-500/15 text-accent-400"><x-icon name="heroicon-o-rocket-launch" class="size-6" /></span>
                        <span><span class="block text-xs text-slate-400">Launch in</span><span class="text-sm font-bold text-white">24–48 hours</span></span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tech stack strip --}}
        <div class="container-x relative pb-10">
            <div class="relative overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_12%,black_88%,transparent)]" aria-label="Technologies we use">
                <div class="animate-marquee flex w-max items-center gap-3">
                    @foreach (array_merge(...array_fill(0, 2, ['Laravel', 'React', 'Vue.js', 'Node.js', 'Flutter', 'Next.js', 'MySQL', 'PostgreSQL', 'Redis', 'Docker', 'AWS', 'cPanel', 'Tailwind CSS', 'REST API'])) as $tech)
                        <span class="shrink-0 rounded-lg border border-white/10 bg-white/[0.03] px-3.5 py-1.5 font-mono text-xs text-slate-400" @if ($loop->index >= 14) aria-hidden="true" @endif><span class="text-brand-400">#</span>{{ $tech }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Stats strip --}}
        @if ($stats)
            <div class="container-x relative pb-16">
                <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-3xl border border-white/10 bg-white/10 lg:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="flex flex-col-reverse bg-ink-900/80 p-6 text-center backdrop-blur sm:p-8" x-data="countUp(@js($stat['value'] ?? ''))" x-intersect.once="start()">
                            <dt class="mt-1 font-mono text-xs tracking-wider text-slate-400 uppercase">{{ $stat['label'] ?? '' }}</dt>
                            <dd class="gradient-text text-3xl font-extrabold sm:text-4xl" x-text="display">{{ $stat['value'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </section>

    {{-- ===================== CLIENT LOGOS ===================== --}}
    @if ($clients->isNotEmpty())
        <section class="border-y border-slate-200 py-10 dark:border-white/5" aria-label="Our clients">
            <p class="text-center text-sm font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400">Trusted by growing businesses</p>
            <div class="relative mt-6 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
                <div class="animate-marquee flex w-max items-center gap-14 hover:[animation-play-state:paused]">
                    @foreach ($clients->concat($clients) as $client)
                        <a href="{{ $client->url ?: '#' }}" @if ($client->url) target="_blank" rel="noopener nofollow" @endif class="shrink-0 opacity-60 grayscale transition hover:opacity-100 hover:grayscale-0" @if ($loop->index >= $clients->count()) aria-hidden="true" tabindex="-1" @endif>
                            @if ($client->logo)
                                <img src="{{ media_url($client->logo) }}" alt="{{ $client->name }}" class="h-9 w-auto dark:brightness-0 dark:invert" loading="lazy" height="36">
                            @else
                                <span class="text-xl font-extrabold tracking-tight text-slate-400">{{ $client->name }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== PRODUCTS ===================== --}}
    @if ($products->isNotEmpty())
        <section class="section" x-data="{ tab: 'all' }">
            <div class="container-x">
                <x-section-heading eyebrow="Our Products" title="Software that's <span class='gradient-text'>ready to launch</span>" subtitle="SaaS subscriptions, one-time licenses, e-commerce stores and web apps — tested, documented and supported by our team." />
                <div class="mt-8 flex justify-center">
                    <div class="flex max-w-full gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-1.5 dark:border-white/10 dark:bg-ink-900/60" role="tablist" aria-label="Filter products">
                        <button type="button" role="tab" @click="tab = 'all'" :aria-selected="tab === 'all'" :class="tab === 'all' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/25' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10'" class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition">All</button>
                        @foreach ($productTypes as $key => $meta)
                            @if ($products->contains('type', $key))
                                <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/25' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10'" class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition">{{ $meta['label'] }}</button>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal>
                    @foreach ($products as $product)
                        <div x-show="tab === 'all' || tab === '{{ $product->type }}'" x-transition.opacity.duration.300ms>
                            <x-cards.product :product="$product" />
                        </div>
                    @endforeach
                </div>
                <div class="mt-12 text-center">
                    <a href="{{ route('products.index') }}" class="btn-outline">View all products <x-icon name="heroicon-m-arrow-right" class="size-4" /></a>
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== SERVICES ===================== --}}
    @if ($services->isNotEmpty())
        <section class="section section-alt isolate overflow-hidden">
            <x-circuit-bg />
            <div class="container-x">
                <x-section-heading eyebrow="What We Do" title="End-to-end <span class='gradient-text'>digital services</span>" subtitle="From idea to launch and beyond — design, development, hosting and growth under one roof." />
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal>
                    @foreach ($services as $service)
                        <x-cards.service :service="$service" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== PROCESS ===================== --}}
    @if ($process)
        <section class="section">
            <div class="container-x">
                <x-section-heading eyebrow="How We Work" title="A simple, <span class='gradient-text'>transparent process</span>" subtitle="You always know what's happening, what's next and when it will be ready." />
                <ol class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal>
                    @foreach ($process as $i => $step)
                        <li class="card relative p-6">
                            <span class="gradient-text text-5xl font-black opacity-90">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-4 text-lg font-bold">{{ $step['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $step['description'] ?? '' }}</p>
                            @unless ($loop->last)
                                <x-icon name="heroicon-o-arrow-long-right" class="absolute top-10 -right-5 hidden size-8 text-brand-300 lg:block dark:text-brand-700" />
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ===================== HOSTING ===================== --}}
    @if ($hostingPlans->isNotEmpty())
        <section class="section section-alt" x-data="{ cycle: 'yearly' }">
            <div class="container-x">
                <x-section-heading eyebrow="Web Hosting" title="Fast, secure hosting <span class='gradient-text'>for every site</span>" subtitle="SSD/NVMe storage, free SSL, daily backups and local support — perfect for business websites and online stores." />
                <div class="mt-8 flex justify-center">
                    <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 dark:border-white/10 dark:bg-ink-900">
                        <button type="button" @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-brand-600 text-white' : 'text-slate-600 dark:text-slate-300'" class="rounded-lg px-5 py-2 text-sm font-semibold transition">Monthly</button>
                        <button type="button" @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-brand-600 text-white' : 'text-slate-600 dark:text-slate-300'" class="rounded-lg px-5 py-2 text-sm font-semibold transition">Yearly <span class="ml-1 text-xs text-emerald-500" :class="cycle === 'yearly' && 'text-emerald-200'">Save more</span></button>
                    </div>
                </div>
                <div class="mx-auto mt-12 grid max-w-5xl gap-8 lg:grid-cols-3" data-reveal>
                    @foreach ($hostingPlans as $plan)
                        <x-cards.hosting :plan="$plan" />
                    @endforeach
                </div>
                <p class="mt-10 text-center"><a href="{{ route('hosting') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-300">Compare all hosting plans →</a></p>
            </div>
        </section>
    @endif

    {{-- ===================== WHY US ===================== --}}
    @if ($whyUs)
        <section class="section">
            <div class="container-x grid items-center gap-14 lg:grid-cols-2">
                <div>
                    <x-section-heading align="left" eyebrow="Why Choose Us" title="A partner who <span class='gradient-text'>ships, supports and scales</span> with you" subtitle="We don't disappear after delivery. Your software, hosting and domain live with one accountable team." />
                    <a href="{{ route('about') }}" class="btn-primary mt-8">More about us <x-icon name="heroicon-m-arrow-right" class="size-4" /></a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($whyUs as $item)
                        <div class="card p-6">
                            <span class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                <x-icon :name="$item['icon'] ?? null" class="size-6" />
                            </span>
                            <h3 class="mt-4 font-bold">{{ $item['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $item['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== PORTFOLIO ===================== --}}
    @if ($portfolios->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x">
                <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                    <x-section-heading align="left" eyebrow="Our Work" title="Recent <span class='gradient-text'>projects</span>" />
                    <a href="{{ route('portfolio.index') }}" class="btn-outline shrink-0">View portfolio <x-icon name="heroicon-m-arrow-right" class="size-4" /></a>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal>
                    @foreach ($portfolios as $item)
                        <x-cards.portfolio :item="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== TESTIMONIALS ===================== --}}
    @if ($testimonials->isNotEmpty())
        <section class="section">
            <div class="container-x">
                <x-section-heading eyebrow="Testimonials" title="Loved by <span class='gradient-text'>our clients</span>" />
                <div class="mt-14 columns-1 gap-6 sm:columns-2 lg:columns-3">
                    @foreach ($testimonials as $t)
                        <figure class="card mb-6 break-inside-avoid p-6">
                            <div class="flex gap-0.5 text-amber-400" aria-label="{{ $t->rating }} out of 5 stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <x-icon name="heroicon-s-star" @class(['size-5', 'opacity-25' => $i > $t->rating]) />
                                @endfor
                            </div>
                            <blockquote class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-slate-300">“{{ $t->content }}”</blockquote>
                            <figcaption class="mt-5 flex items-center gap-3">
                                @if ($t->photo)
                                    <img src="{{ media_url($t->photo) }}" alt="{{ $t->name }}" class="size-11 rounded-full object-cover" loading="lazy" width="44" height="44">
                                @else
                                    <span class="grid size-11 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-violet-600 font-bold text-white">{{ mb_substr($t->name, 0, 1) }}</span>
                                @endif
                                <span>
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ $t->name }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ collect([$t->designation, $t->company])->filter()->implode(', ') }}</span>
                                </span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== BLOG ===================== --}}
    @if ($posts->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x">
                <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                    <x-section-heading align="left" eyebrow="From the Blog" title="Insights &amp; <span class='gradient-text'>guides</span>" />
                    <a href="{{ route('blog.index') }}" class="btn-outline shrink-0">All articles <x-icon name="heroicon-m-arrow-right" class="size-4" /></a>
                </div>
                <div class="mt-12 grid gap-6 md:grid-cols-3" data-reveal>
                    @foreach ($posts as $post)
                        <x-cards.post :post="$post" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== FAQ ===================== --}}
    @if ($faqs->isNotEmpty())
        <section class="section">
            <div class="container-x grid gap-12 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <x-section-heading align="left" eyebrow="FAQ" title="Questions? <span class='gradient-text'>Answers.</span>" subtitle="Can't find what you're looking for? Our team replies within a few hours." />
                    <a href="{{ route('faq') }}" class="btn-outline mt-8">See all FAQs</a>
                </div>
                <div class="lg:col-span-3">
                    <x-faq-list :faqs="$faqs" />
                </div>
            </div>
        </section>
    @endif

    <x-cta />
</x-layouts.app>
