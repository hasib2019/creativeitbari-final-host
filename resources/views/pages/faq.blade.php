@php
    $schema = $faqs->isEmpty() ? [] : [[
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->all(),
    ]];
@endphp
<x-layouts.app title="Frequently Asked Questions" description="Answers to common questions about our software products, custom development, hosting, domains, payments and support." :schema="$schema">
    <x-page-hero title="Frequently asked <span class='gradient-text'>questions</span>" subtitle="Everything you need to know about our products, services, hosting and support." eyebrow="FAQ" :breadcrumbs="['FAQ' => null]" />

    <section class="section">
        <div class="container-x max-w-3xl space-y-14">
            @foreach ($groups as $group => $items)
                <div>
                    <h2 class="mb-6 text-2xl font-extrabold">{{ $group }}</h2>
                    <x-faq-list :faqs="$items" />
                </div>
            @endforeach
        </div>
    </section>

    <x-cta title="Still have questions?" subtitle="Our team is happy to help — call, WhatsApp or send us a message." button-text="Contact Us" :button-url="route('contact')" />
</x-layouts.app>
