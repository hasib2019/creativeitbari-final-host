@php
    $paid = $payment->isPaid();
@endphp
<x-layouts.app :title="$paid ? 'Payment complete' : 'Payment link closed'" :noindex="true">
    <section class="section">
        <div class="container-x max-w-xl text-center">
            @if ($paid)
                <span class="mx-auto grid size-20 place-items-center rounded-full bg-emerald-500/15 text-emerald-500"><x-icon name="heroicon-o-check-circle" class="size-12" /></span>
                <h1 class="mt-6 text-3xl font-extrabold sm:text-4xl">Payment complete</h1>
                <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
                    {{ money($payment->amount) }} received for <strong>{{ $payment->description }}</strong>@if ($payment->payment_method) via {{ $payment->payment_method }}@endif.
                </p>
            @else
                <span class="mx-auto grid size-20 place-items-center rounded-full bg-slate-500/15 text-slate-500"><x-icon name="heroicon-o-clock" class="size-12" /></span>
                <h1 class="mt-6 text-3xl font-extrabold sm:text-4xl">This payment link is closed</h1>
                <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">It has {{ $payment->status === 'expired' || $payment->isExpired() ? 'expired' : 'been '.$payment->status }}. Please start the payment again from {{ $payment->merchantApp->name }}.</p>
            @endif

            <p class="mt-3 font-mono text-xs text-slate-500">Ref. {{ $payment->reference }}@if ($payment->gateway_transaction_id) · EPS {{ $payment->gateway_transaction_id }}@endif</p>

            <div class="mt-8">
                <a href="{{ $returnUrl }}" class="btn-primary">Return to {{ $payment->merchantApp->name }}</a>
            </div>
        </div>
    </section>
</x-layouts.app>
