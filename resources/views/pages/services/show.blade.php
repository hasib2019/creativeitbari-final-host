@php
    $schema = [[
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $service->title,
        'description' => $service->short_description,
        'serviceType' => $service->title,
        'url' => $service->url(),
        'provider' => ['@id' => url('/').'#organization'],
        'areaServed' => 'Bangladesh',
        'image' => media_url($service->image),
    ]];
@endphp
<x-layouts.app :title="$service->meta_title ?: $service->title" :description="$service->meta_description ?: $service->short_description" :image="$service->image" :schema="$schema">
    <x-page-hero :title="e($service->title)" :subtitle="$service->short_description" eyebrow="Service" :breadcrumbs="['Services' => route('services.index'), $service->title => null]">
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('quote', ['service' => $service->title]) }}" class="btn-primary">Get a Free Quote <x-icon name="heroicon-m-arrow-right" class="size-4" /></a>
            <a href="{{ route('portfolio.index') }}" class="btn-outline">See our work</a>
        </div>
    </x-page-hero>

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @if ($service->image)
                    <img src="{{ media_url($service->image) }}" alt="{{ $service->title }}" class="mb-10 w-full rounded-3xl object-cover" width="960" height="540">
                @endif
                <article class="prose prose-slate max-w-none dark:prose-invert prose-headings:font-extrabold prose-a:text-brand-600 prose-img:rounded-2xl">
                    {!! $service->content !!}
                </article>
            </div>
            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                @if ($service->features)
                    <div class="card p-6">
                        <h2 class="text-lg font-bold">What's included</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($service->features as $feature)
                                <li class="flex gap-2"><x-icon name="heroicon-s-check-circle" class="size-5 shrink-0 text-emerald-500" /> {{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card p-6">
                    <h2 class="text-lg font-bold">Other services</h2>
                    <ul class="mt-3 divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($others as $other)
                            <li><a href="{{ $other->url() }}" class="flex items-center gap-3 py-3 text-sm font-medium hover:text-brand-600 dark:hover:text-brand-300"><x-icon :name="$other->icon" class="size-5 text-brand-500" /> {{ $other->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="card bg-gradient-to-br from-brand-600 to-violet-700 p-6 text-white">
                    <h2 class="text-lg font-bold text-white">Let's talk about your project</h2>
                    <p class="mt-2 text-sm text-indigo-100">Free consultation. Reply within 24 hours.</p>
                    <a href="{{ route('quote', ['service' => $service->title]) }}" class="btn mt-5 w-full bg-white text-brand-700 hover:bg-indigo-50">Get a Quote</a>
                </div>
            </aside>
        </div>
    </section>

    @if ($faqs->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x max-w-3xl">
                <x-section-heading eyebrow="FAQ" title="Frequently asked questions" />
                <div class="mt-10"><x-faq-list :faqs="$faqs" /></div>
            </div>
        </section>
    @endif

    <x-cta />
</x-layouts.app>
