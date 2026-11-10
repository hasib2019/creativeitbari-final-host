@props([
    'title' => null,
    'subtitle' => null,
    'buttonText' => null,
    'buttonUrl' => null,
])
<section class="section">
    <div class="container-x">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 via-violet-700 to-indigo-900 px-6 py-14 text-center shadow-2xl shadow-brand-900/30 sm:px-12 sm:py-16">
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-30 [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
            <div class="pointer-events-none absolute -top-20 -right-20 size-72 rounded-full bg-cyan-400/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-16 size-72 rounded-full bg-fuchsia-500/30 blur-3xl"></div>
            <div class="relative mx-auto max-w-2xl">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $title ?: setting('cta_title', 'Ready to build something great?') }}</h2>
                <p class="mt-4 text-base text-indigo-100 sm:text-lg">{{ $subtitle ?: setting('cta_subtitle', 'Tell us about your idea — we will reply within 24 hours with a plan and a quote.') }}</p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ $buttonUrl ?: setting('cta_button_url', route('quote')) }}" class="btn bg-white text-brand-700 shadow-lg hover:-translate-y-0.5 hover:bg-indigo-50">
                        {{ $buttonText ?: setting('cta_button_text', 'Start Your Project') }} <x-icon name="heroicon-m-arrow-right" class="size-4" />
                    </a>
                    @if ($phone = setting('phone'))
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="btn border border-white/30 text-white hover:bg-white/10">
                            <x-icon name="heroicon-o-phone" class="size-4" /> {{ $phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
