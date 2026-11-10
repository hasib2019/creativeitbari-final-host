<x-layouts.app title="Thank You" :noindex="true">
    <section class="section">
        <div class="container-x max-w-xl text-center">
            <span class="mx-auto grid size-20 place-items-center rounded-full bg-emerald-500/15 text-emerald-500"><x-icon name="heroicon-o-check-circle" class="size-12" /></span>
            <h1 class="mt-6 text-3xl font-extrabold sm:text-4xl">Thank you!</h1>
            <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
                {{ in_array($type, ['order', 'domain', 'hosting']) ? 'Your order request has been received. Our team will contact you shortly to confirm and share payment details.' : 'Your message has been received. We usually reply within a few hours on working days.' }}
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn-primary">Back to Home</a>
                @if ($wa = setting('whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" target="_blank" rel="noopener" class="btn-outline">Chat on WhatsApp</a>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
