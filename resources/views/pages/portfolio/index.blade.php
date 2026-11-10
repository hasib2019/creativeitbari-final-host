<x-layouts.app title="Portfolio" description="Explore software, web application, e-commerce and website projects we have delivered for businesses in Bangladesh and abroad.">
    <x-page-hero title="Work we're <span class='gradient-text'>proud of</span>" subtitle="A selection of software, web apps and online stores we've designed, built and launched." eyebrow="Portfolio" :breadcrumbs="['Portfolio' => null]" />

    <section class="section pt-10">
        <div class="container-x">
            @if ($categories->isNotEmpty())
                <nav class="flex justify-center gap-2 overflow-x-auto pb-1" aria-label="Portfolio categories">
                    <a href="{{ route('portfolio.index') }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition', 'bg-brand-600 text-white' => ! $category, 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $category])>All</a>
                    @foreach ($categories as $c)
                        <a href="{{ route('portfolio.index', ['category' => $c]) }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition', 'bg-brand-600 text-white' => $category === $c, 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $category !== $c])>{{ $c }}</a>
                    @endforeach
                </nav>
            @endif
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($items as $item)
                    <x-cards.portfolio :item="$item" />
                @empty
                    <p class="col-span-full text-center text-slate-500">No projects in this category yet.</p>
                @endforelse
            </div>
            <div class="mt-12">{{ $items->links() }}</div>
        </div>
    </section>

    <x-cta />
</x-layouts.app>
