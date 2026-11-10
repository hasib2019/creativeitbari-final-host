<x-layouts.app title="Our Services" description="Custom software, web application, e-commerce and mobile app development, UI/UX design and SEO services in Bangladesh.">
    <x-page-hero title="Services that move your <span class='gradient-text'>business forward</span>" subtitle="Design, development, hosting and growth — one accountable team from idea to launch and beyond." eyebrow="Services" :breadcrumbs="['Services' => null]" />

    <section class="section">
        <div class="container-x grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <x-cards.service :service="$service" />
            @endforeach
        </div>
    </section>

    <x-cta />
</x-layouts.app>
