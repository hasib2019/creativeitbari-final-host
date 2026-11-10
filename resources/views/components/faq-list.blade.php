@props(['faqs'])
<div class="space-y-3" x-data="{ active: 0 }">
    @foreach ($faqs as $faq)
        <div class="card overflow-hidden" :class="active === {{ $loop->index }} && 'border-brand-300 dark:border-brand-500/40'">
            <h3 class="text-base">
                <button type="button" @click="active = active === {{ $loop->index }} ? null : {{ $loop->index }}"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold text-slate-900 dark:text-white"
                        :aria-expanded="active === {{ $loop->index }}" aria-controls="faq-{{ $faq->id }}">
                    <span>{{ $faq->question }}</span>
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 transition dark:bg-white/5 dark:text-slate-300" :class="active === {{ $loop->index }} && 'rotate-45 !bg-brand-600 !text-white'">
                        <x-icon name="heroicon-m-plus" class="size-4" />
                    </span>
                </button>
            </h3>
            <div id="faq-{{ $faq->id }}" x-show="active === {{ $loop->index }}" x-collapse @if (! $loop->first) x-cloak @endif>
                <div class="px-5 pb-5 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{!! nl2br(e($faq->answer)) !!}</div>
            </div>
        </div>
    @endforeach
</div>
