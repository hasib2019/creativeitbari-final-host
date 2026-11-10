@php
    $periodLabel = match ($item['period'] ?? null) { 'monthly' => '/month', 'yearly' => '/year', 'lifetime' => ' lifetime', 'one_time' => ' one-time', default => '' };
@endphp
<x-layouts.app title="Order {{ $item['title'] }}" description="Complete your order request." :noindex="true">
    <x-page-hero title="Complete your <span class='gradient-text'>order</span>" subtitle="Submit your details and our team will confirm your order and share payment instructions (bKash, Nagad, bank or card)." :breadcrumbs="['Order' => null]" />

    <section class="section">
        <div class="container-x grid gap-10 lg:grid-cols-5">
            <div class="card p-6 sm:p-8 lg:col-span-3">
                <form action="{{ route('inquiries.store') }}" method="POST">
                    <input type="hidden" name="type" value="{{ $item['type'] }}">
                    <input type="hidden" name="item_name" value="{{ $item['title'] }}{{ isset($item['price']) ? ' — '.money($item['price']).$periodLabel : '' }}">
                    <x-inquiry-fields :message-required="false" :phone-required="true" message-label="Notes (optional)" />
                    <button class="btn-primary mt-6 w-full sm:w-auto">Place Order Request <x-icon name="heroicon-m-check" class="size-4" /></button>
                    <p class="mt-3 text-xs text-slate-500">No payment is taken now. We'll contact you to confirm.</p>
                </form>
            </div>
            <aside class="lg:col-span-2">
                <div class="card sticky top-28 p-6">
                    <h2 class="text-lg font-bold">Order summary</h2>
                    <div class="mt-5 rounded-xl bg-slate-50 p-4 dark:bg-white/5">
                        <p class="text-xs font-semibold tracking-wider text-brand-600 uppercase dark:text-brand-300">{{ $item['subtitle'] }}</p>
                        <p class="mt-1 font-bold text-slate-900 dark:text-white">{{ $item['title'] }}</p>
                    </div>
                    @if (isset($item['price']))
                        <div class="mt-5 flex items-center justify-between border-t border-slate-200 pt-5 dark:border-white/10">
                            <span class="font-semibold">Total</span>
                            <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ money($item['price']) }}<span class="text-sm font-normal text-slate-500">{{ $periodLabel }}</span></span>
                        </div>
                    @endif
                    <a href="{{ $item['url'] }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:underline dark:text-brand-300"><x-icon name="heroicon-m-arrow-left" class="size-4" /> Change selection</a>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
