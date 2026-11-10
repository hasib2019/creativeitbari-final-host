@php
    $title = $category ? $category->name : 'Blog';
    $desc = $category?->description ?: 'Guides and insights on software, web development, e-commerce, hosting and growing your business online.';
@endphp
<x-layouts.app :title="$title" :description="$desc" :noindex="$search !== ''">
    <x-page-hero :title="$category ? e($category->name) : 'Insights &amp; <span class=\'gradient-text\'>guides</span>'" :subtitle="$desc" eyebrow="Blog" :breadcrumbs="$category ? ['Blog' => route('blog.index'), $category->name => null] : ['Blog' => null]">
        <form action="{{ route('blog.index') }}" class="mx-auto mt-8 flex max-w-lg gap-2" role="search">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search articles…" class="input" aria-label="Search articles">
            <button class="btn-primary">Search</button>
        </form>
    </x-page-hero>

    <section class="section pt-10">
        <div class="container-x">
            @if ($categories->isNotEmpty())
                <nav class="flex justify-center gap-2 overflow-x-auto pb-1" aria-label="Blog categories">
                    <a href="{{ route('blog.index') }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition', 'bg-brand-600 text-white' => ! $category, 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $category])>All</a>
                    @foreach ($categories as $c)
                        <a href="{{ route('blog.category', $c) }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition', 'bg-brand-600 text-white' => $category?->is($c), 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => ! $category?->is($c)])>{{ $c->name }} <span class="opacity-60">{{ $c->posts_count }}</span></a>
                    @endforeach
                </nav>
            @endif

            @if ($search !== '')
                <p class="mt-8 text-center text-slate-500">{{ $posts->total() }} result(s) for “{{ $search }}”</p>
            @endif

            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <x-cards.post :post="$post" />
                @empty
                    <p class="col-span-full py-10 text-center text-slate-500">No articles found.</p>
                @endforelse
            </div>
            <div class="mt-12">{{ $posts->links() }}</div>
        </div>
    </section>
</x-layouts.app>
