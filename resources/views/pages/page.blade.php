<x-layouts.app :title="$page->meta_title ?: $page->title" :description="$page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 160)">
    <x-page-hero :title="e($page->title)" :breadcrumbs="[$page->title => null]">
        <p class="mt-4 text-sm text-slate-500">Last updated: {{ $page->updated_at->format('F d, Y') }}</p>
    </x-page-hero>
    <section class="section">
        <article class="container-x prose prose-slate max-w-3xl dark:prose-invert prose-headings:font-extrabold prose-a:text-brand-600">
            {!! $page->content !!}
        </article>
    </section>
</x-layouts.app>
