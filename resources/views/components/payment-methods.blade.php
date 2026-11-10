{{--
    The EPS "Pay with" artwork: every wallet, card and bank EPS takes.
    variant="strip": one long row from lg up, the two-row grid below it (the strip is unreadable on a phone).
    variant="grid": always the two-row grid.  variant="badge": the compact "Pay with EPS … +others".
    Light and dark artwork follow the site theme unless theme="light" / "dark" pins one.
--}}
@props(['variant' => 'strip', 'theme' => null, 'class' => ''])
@php
    $alt = 'Pay with VISA, Mastercard, American Express, DBBL Nexus, UnionPay, Rocket, OK Wallet, bKash, Tap, Meghna Pay, Nagad, Islami Bank mCash, Upay and Cellfin — verified by EPS';
    $themes = $theme ? [$theme] : ['light', 'dark'];
    $visibility = fn (string $t) => $theme ? '' : ($t === 'light' ? 'dark:hidden' : 'hidden dark:block');
    $src = fn (string $name) => asset('images/payments/'.$name.'.webp');
@endphp
@foreach ($themes as $t)
    @if ($variant === 'badge')
        <img src="{{ $src('eps-badge') }}" alt="{{ $alt }}" width="1000" height="172" loading="lazy" decoding="async"
             {{ $attributes->class(['h-auto w-full', $visibility($t), $class]) }}>
    @elseif ($variant === 'grid')
        <img src="{{ $src('eps-grid-'.$t) }}" alt="{{ $alt }}" width="1400" height="{{ $t === 'light' ? 251 : 246 }}" loading="lazy" decoding="async"
             {{ $attributes->class(['h-auto w-full', $visibility($t), $class]) }}>
    @else
        <picture {{ $attributes->class([$visibility($t) ?: 'block', $class]) }}>
            <source media="(min-width: 1024px)" srcset="{{ $src('eps-strip-'.$t) }}" width="2400" height="123">
            <img src="{{ $src('eps-grid-'.$t) }}" alt="{{ $alt }}" width="1400" height="{{ $t === 'light' ? 251 : 246 }}" loading="lazy" decoding="async" class="mx-auto h-auto w-full max-w-2xl lg:max-w-none">
        </picture>
    @endif
@endforeach
