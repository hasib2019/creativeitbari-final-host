<button type="button" x-data @click="$store.theme.toggle()"
        {{ $attributes->class('grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:border-brand-400 hover:text-brand-600 dark:border-white/10 dark:text-slate-300 dark:hover:border-brand-400 dark:hover:text-white') }}
        :aria-label="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'" aria-label="Toggle dark mode">
    <x-icon name="heroicon-o-sun" class="hidden size-5 dark:block" />
    <x-icon name="heroicon-o-moon" class="size-5 dark:hidden" />
</button>
