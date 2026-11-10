@props(['item'])
<article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-ink-900">
    <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-100 to-cyan-100 dark:from-brand-950 dark:to-slate-900">
        @if ($item->thumbnail)
            <img src="{{ media_url($item->thumbnail) }}" alt="{{ $item->title }}" loading="lazy" width="640" height="480" class="size-full object-cover object-left-top transition duration-700 group-hover:scale-110">
        @else
            <div class="grid size-full place-items-center"><x-icon name="heroicon-o-photo" class="size-16 text-brand-400/60" /></div>
        @endif
    </div>
    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/95 via-slate-950/70 to-transparent p-5 pt-16">
        @if ($item->category)
            <span class="text-xs font-semibold tracking-wide text-cyan-300 uppercase">{{ $item->category }}</span>
        @endif
        <h3 class="mt-1 text-lg font-bold text-white"><a href="{{ $item->url() }}" class="after:absolute after:inset-0">{{ $item->title }}</a></h3>
        @if ($item->client_name)
            <p class="text-sm text-slate-300">{{ $item->client_name }}</p>
        @endif
    </div>
</article>
