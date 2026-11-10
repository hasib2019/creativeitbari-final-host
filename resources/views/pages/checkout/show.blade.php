@php
    $brand = config('payment.brand') ?: setting('site_name', config('app.name')).' Pay';
    $field = fn (string $name) => old($name, $payment->{'customer_'.$name});
@endphp
<x-layouts.app title="Checkout — {{ $payment->description }}" description="Secure payment" :noindex="true">
    <section class="section">
        <div class="container-x max-w-5xl">
            <div class="mb-8 text-center">
                <span class="badge-brand"><x-icon name="heroicon-m-lock-closed" class="size-3.5" /> Secure checkout · {{ $brand }}</span>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Complete your payment</h1>
                <p class="mt-3 text-slate-600 dark:text-slate-400">Pay for <strong>{{ $payment->merchantApp->name }}</strong> with bKash, Nagad, Rocket, card or internet banking.</p>
            </div>

            @if (session('checkout_error'))
                <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200" role="alert">
                    {{ session('checkout_error') }}
                    @if (session('checkout_error_detail'))
                        <span class="mt-1 block font-mono text-xs opacity-75">{{ session('checkout_error_detail') }}</span>
                    @endif
                </div>
            @endif
            @if (session('checkout_notice') || $payment->status === 'processing')
                <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100" role="status">
                    {{ session('checkout_notice') ?: 'A payment for this order was started and is not confirmed yet. If money was taken, wait a minute and check again — do not pay twice.' }}
                    <a href="{{ route('checkout.show', $payment) }}" class="ml-1 font-semibold underline">Check again</a>
                </div>
            @endif

            <div class="grid gap-8 lg:grid-cols-5">
                <div class="card p-6 sm:p-8 lg:col-span-3">
                    <h2 class="text-lg font-bold">Billing details</h2>
                    <p class="mt-1 text-sm text-slate-500">Required by the payment gateway for the receipt.</p>

                    <form action="{{ route('checkout.pay', $payment) }}" method="POST" class="mt-6" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ([
                                ['name', 'Full name', 'text', 'name', true],
                                ['email', 'Email', 'email', 'email', true],
                                ['phone', 'Mobile number', 'tel', 'tel', true],
                                ['address', 'Address', 'text', 'street-address', true],
                                ['city', 'City', 'text', 'address-level2', true],
                                ['state', 'District / State', 'text', 'address-level1', false],
                                ['postcode', 'Post code', 'text', 'postal-code', true],
                            ] as [$name, $label, $type, $autocomplete, $required])
                                <div @class(['sm:col-span-2' => $name === 'address'])>
                                    <label for="c-{{ $name }}" class="label">{{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif</label>
                                    <input id="c-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $field($name) }}" @required($required) maxlength="{{ $name === 'address' ? 255 : 150 }}" autocomplete="{{ $autocomplete }}" class="input" @if ($name === 'phone') placeholder="01XXXXXXXXX" @endif>
                                    @error($name)<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                            <div>
                                <label for="c-country" class="label">Country <span class="text-rose-500">*</span></label>
                                <input id="c-country" name="country" value="{{ $field('country') ?: 'BD' }}" required maxlength="60" autocomplete="country" class="input">
                                @error('country')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <button class="btn-primary mt-8 w-full" :disabled="busy" :class="busy && 'opacity-60'">
                            <x-icon name="heroicon-m-lock-closed" class="size-4" />
                            <span x-text="busy ? 'Connecting to the payment gateway…' : 'Pay {{ money($payment->amount) }}'">Pay {{ money($payment->amount) }}</span>
                        </button>
                    </form>

                    <x-payment-methods variant="grid" class="mt-6" />

                    <form action="{{ route('checkout.cancel', $payment) }}" method="POST" class="mt-4 text-center">
                        @csrf
                        <button class="text-sm font-medium text-slate-500 hover:text-slate-800 hover:underline dark:hover:text-slate-200">Cancel and return to {{ $payment->merchantApp->name }}</button>
                    </form>
                </div>

                <aside class="lg:col-span-2">
                    <div class="card sticky top-28 p-6">
                        <h2 class="text-lg font-bold">Order summary</h2>
                        <div class="mt-5 rounded-xl bg-slate-50 p-4 dark:bg-white/5">
                            <p class="text-xs font-semibold tracking-wider text-brand-600 uppercase dark:text-brand-300">{{ $payment->merchantApp->name }}</p>
                            <p class="mt-1 font-bold text-slate-900 dark:text-white">{{ $payment->description }}</p>
                        </div>
                        <dl class="mt-5 space-y-2 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Order</dt><dd class="font-mono text-xs">{{ $payment->order_id }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-slate-500">Payment ref.</dt><dd class="font-mono text-xs">{{ $payment->reference }}</dd></div>
                        </dl>
                        <div class="mt-5 flex items-center justify-between border-t border-slate-200 pt-5 dark:border-white/10">
                            <span class="font-semibold">Total</span>
                            <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ money($payment->amount) }}</span>
                        </div>
                        <p class="mt-5 flex items-start gap-2 text-xs text-slate-500">
                            <x-icon name="heroicon-m-shield-check" class="mt-0.5 size-4 shrink-0 text-emerald-500" />
                            Payments are processed by EPS (Easy Payment System). Card and wallet details are entered on the EPS page, never here.
                        </p>
                        <x-payment-methods variant="badge" theme="light" class="mt-4 rounded-xl" />
                    </div>
                </aside>
            </div>
        </div>
    </section>
</x-layouts.app>
