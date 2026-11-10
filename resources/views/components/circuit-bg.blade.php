{{-- Circuit-board traces behind a section, with small light pulses running along them. --}}
@props(['class' => ''])
<svg {{ $attributes->class(['pointer-events-none absolute inset-0 -z-10 size-full text-brand-500/15 dark:text-brand-400/10', $class]) }} aria-hidden="true" preserveAspectRatio="xMidYMid slice" viewBox="0 0 1200 600" fill="none">
    @php
        $traces = [
            'M0 80 H220 L260 120 H520 L560 80 H760',
            'M1200 140 H980 L940 180 H700 L660 220 H420',
            'M0 300 H140 L180 260 H380 L420 300 H640 L680 340 H900',
            'M1200 420 H1040 L1000 380 H780 L740 420 H520',
            'M0 520 H300 L340 480 H600 L640 520 H1200',
            'M860 0 V120 L900 160 V320',
            'M300 600 V460 L260 420 V340',
        ];
    @endphp
    @foreach ($traces as $i => $d)
        <path d="{{ $d }}" stroke="currentColor" stroke-width="1.5" />
        <path d="{{ $d }}" class="circuit-pulse" stroke="{{ $i % 3 === 1 ? '#e5233c' : '#13b5cb' }}" stroke-width="2.5" stroke-linecap="round" style="--d: {{ 5 + $i }}s; --delay: -{{ $i * 1.3 }}s" />
    @endforeach
    @foreach ([[220, 80], [760, 80], [420, 220], [900, 340], [520, 420], [600, 520], [900, 320], [260, 340]] as [$cx, $cy])
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4" fill="currentColor" />
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="9" stroke="currentColor" stroke-width="1.2" />
    @endforeach
</svg>
