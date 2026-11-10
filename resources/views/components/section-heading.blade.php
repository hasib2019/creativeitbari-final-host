@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'as' => 'h2'])
<div {{ $attributes->class(['max-w-3xl', 'mx-auto text-center' => $align === 'center']) }} data-reveal>
    @if ($eyebrow)
        <span class="badge-brand mb-4"><span class="text-accent-500 dark:text-accent-400">//</span> {{ $eyebrow }}</span>
    @endif
    <{{ $as }} class="text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl">{!! $title !!}</{{ $as }}>
    @if ($subtitle)
        <p class="mt-4 text-base leading-relaxed text-slate-600 sm:text-lg dark:text-slate-400">{{ $subtitle }}</p>
    @endif
</div>
