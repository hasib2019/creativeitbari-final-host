<div {{ $attributes->class('space-y-4') }}>
    @foreach (array_filter([
        ['heroicon-o-phone', 'Call us', setting('phone'), setting('phone') ? 'tel:'.preg_replace('/[^\d+]/', '', setting('phone')) : null],
        ['heroicon-o-chat-bubble-left-right', 'WhatsApp', setting('whatsapp') ? '+'.preg_replace('/\D/', '', setting('whatsapp')) : null, setting('whatsapp') ? 'https://wa.me/'.preg_replace('/\D/', '', setting('whatsapp')) : null],
        ['heroicon-o-envelope', 'Email', setting('email'), setting('email') ? 'mailto:'.setting('email') : null],
        ['heroicon-o-map-pin', 'Office', setting('address'), null],
        ['heroicon-o-clock', 'Working hours', setting('working_hours'), null],
    ], fn ($row) => filled($row[2])) as [$icon, $label, $value, $href])
        <div class="card flex gap-4 p-5">
            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300"><x-icon :name="$icon" class="size-6" /></span>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p>
                @if ($href)
                    <a href="{{ $href }}" class="font-semibold text-slate-900 hover:text-brand-600 dark:text-white" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif>{{ $value }}</a>
                @else
                    <p class="font-semibold text-slate-900 dark:text-white">{{ $value }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
