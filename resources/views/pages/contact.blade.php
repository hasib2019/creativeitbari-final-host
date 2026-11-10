<x-layouts.app title="Contact Us" description="Get in touch for software, web development, e-commerce, hosting or domain support. Call, WhatsApp, email or visit our Dhaka office.">
    <x-page-hero title="Let's <span class='gradient-text'>talk</span>" subtitle="Questions about a product, a project or your hosting? We usually reply within a few hours." eyebrow="Contact" :breadcrumbs="['Contact' => null]" />

    <section class="section">
        <div class="container-x grid gap-10 lg:grid-cols-5">
            <x-contact-info class="lg:col-span-2" />
            <div class="card p-6 sm:p-8 lg:col-span-3">
                <h2 class="text-2xl font-extrabold">Send us a message</h2>
                <form action="{{ route('inquiries.store') }}" method="POST" class="mt-6">
                    <input type="hidden" name="type" value="contact">
                    <x-inquiry-fields>
                        <div class="sm:col-span-2">
                            <label for="f-subject" class="label">Subject</label>
                            <input id="f-subject" name="subject" value="{{ old('subject') }}" maxlength="200" class="input">
                        </div>
                    </x-inquiry-fields>
                    <button class="btn-primary mt-6 w-full sm:w-auto">Send Message <x-icon name="heroicon-m-paper-airplane" class="size-4" /></button>
                </form>
            </div>
        </div>
    </section>

    @if ($map = setting('google_map_embed'))
        @php
            // Accept either a bare embed URL or a full <iframe> snippet pasted from Google Maps.
            $mapSrc = preg_match('/src="([^"]+)"/', $map, $m) ? $m[1] : $map;
        @endphp
        @if (str_starts_with($mapSrc, 'https://www.google.com/maps'))
            <section class="pb-20">
                <div class="container-x">
                    <iframe src="{{ $mapSrc }}" title="Office location" class="h-96 w-full rounded-3xl border border-slate-200 dark:border-white/10" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </section>
        @endif
    @endif
</x-layouts.app>
