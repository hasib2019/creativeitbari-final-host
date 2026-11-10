@php
    $types = \App\Models\HostingPlan::TYPES;
    $firstType = $plansByType->keys()->first() ?? 'shared';
    $faqSchema = $faqs->isEmpty() ? [] : [[
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->all(),
    ]];
@endphp
<x-layouts.app title="Web Hosting in Bangladesh" description="Fast NVMe SSD web hosting with free SSL, daily backups, cPanel and local support. Shared, WordPress and reseller hosting plans in BDT." :schema="$faqSchema">
    <x-page-hero title="Blazing-fast <span class='gradient-text'>web hosting</span>" subtitle="NVMe SSD, LiteSpeed, free SSL, daily backups and real human support — at prices made for Bangladesh." eyebrow="Hosting" :breadcrumbs="['Web Hosting' => null]" />

    <section class="section pt-12" x-data="{ type: '{{ $firstType }}', cycle: 'yearly' }">
        <div class="container-x">
            <div class="flex flex-col items-center gap-4">
                @if ($plansByType->count() > 1)
                    <div class="flex max-w-full gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-1.5 dark:border-white/10 dark:bg-ink-900/60" role="tablist">
                        @foreach ($plansByType->keys() as $key)
                            <button type="button" role="tab" @click="type = '{{ $key }}'" :aria-selected="type === '{{ $key }}'" :class="type === '{{ $key }}' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/25' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10'" class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition">{{ $types[$key] ?? ucfirst($key) }}</button>
                        @endforeach
                    </div>
                @endif
                <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 dark:border-white/10 dark:bg-ink-900">
                    <button type="button" @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-300'" class="rounded-lg px-5 py-2 text-sm font-semibold transition">Monthly</button>
                    <button type="button" @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-300'" class="rounded-lg px-5 py-2 text-sm font-semibold transition">Yearly <span class="text-xs text-emerald-500">Save more</span></button>
                </div>
            </div>

            @foreach ($plansByType as $key => $plans)
                <div x-show="type === '{{ $key }}'" @if ($key !== $firstType) x-cloak @endif class="mx-auto mt-14 grid max-w-6xl gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        <x-cards.hosting :plan="$plan" />
                    @endforeach
                </div>
            @endforeach

            {{-- Included with every plan --}}
            <div class="mt-20 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['heroicon-o-bolt', 'NVMe + LiteSpeed', 'Up to 20× faster page loads than standard SSD hosting.'],
                    ['heroicon-o-lock-closed', 'Free SSL Forever', 'Auto-installed, auto-renewing HTTPS on every domain.'],
                    ['heroicon-o-cloud-arrow-up', 'Daily Backups', 'One-click restore of files, databases and emails.'],
                    ['heroicon-o-lifebuoy', 'Local Support', 'Bangla & English support via phone, WhatsApp and email.'],
                ] as [$icon, $title, $text])
                    <div class="card p-6">
                        <span class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300"><x-icon :name="$icon" class="size-6" /></span>
                        <h3 class="mt-4 font-bold">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($domains->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x text-center">
                <x-section-heading eyebrow="Domains" title="Pair it with the <span class='gradient-text'>perfect domain</span>" />
                <form action="{{ route('domains.index') }}" class="mx-auto mt-8 flex max-w-2xl gap-2 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-white/10 dark:bg-ink-900" role="search">
                    <input name="domain" placeholder="Search your domain name…" class="min-w-0 flex-1 bg-transparent px-4 text-sm focus:outline-none dark:text-white" aria-label="Domain name">
                    <button class="btn-primary">Search</button>
                </form>
                <div class="mx-auto mt-8 grid max-w-4xl grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($domains as $d)
                        <div class="card p-4"><p class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $d->tld }}</p><p class="mt-1 text-sm text-brand-600 dark:text-brand-300">{{ money($d->register_price) }}/yr</p></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($faqs->isNotEmpty())
        <section class="section">
            <div class="container-x max-w-3xl">
                <x-section-heading eyebrow="FAQ" title="Hosting questions" />
                <div class="mt-10"><x-faq-list :faqs="$faqs" /></div>
            </div>
        </section>
    @endif

    <x-cta title="Moving from another host?" subtitle="We migrate your websites, databases and emails for free — usually with zero downtime." button-text="Contact Support" :button-url="route('contact')" />
</x-layouts.app>
