@props(['size' => 'md'])
@php
    $siteName = setting('site_name', config('app.name'));
    $logo = media_url(setting('logo'));
    $logoDark = media_url(setting('logo_dark')) ?: $logo;
    // Logos are uploaded at 500×200 (5:2): height sets the size, width follows.
    $imgClass = match ($size) {
        'lg' => 'h-16 sm:h-20',
        default => 'h-11 lg:h-14',
    };
@endphp
<a href="{{ route('home') }}" {{ $attributes->class('flex shrink-0 items-center gap-2.5') }} aria-label="{{ $siteName }} — Home">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $siteName }}" class="{{ $imgClass }} w-auto max-w-[220px] object-contain dark:hidden" width="500" height="200">
        <img src="{{ $logoDark }}" alt="{{ $siteName }}" class="{{ $imgClass }} hidden w-auto max-w-[220px] object-contain dark:block" width="500" height="200">
    @else
        @php
            // "CreativeItBari" → "Creative" + gradient "ItBari"; any other name renders plain.
            $parts = preg_split('/(?=It)/i', $siteName, 2);
        @endphp
        <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-accent-500 via-fuchsia-600 to-brand-500 text-lg font-black text-white shadow-lg shadow-accent-600/30">{{ mb_substr($siteName, 0, 1) }}</span>
        <span class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $parts[0] }}@isset($parts[1])<span class="gradient-text">{{ $parts[1] }}</span>@endisset</span>
    @endif
</a>
