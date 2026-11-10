@props(['post'])
<article class="card-hover group flex flex-col overflow-hidden">
    <a href="{{ $post->url() }}" class="block aspect-[16/9] overflow-hidden bg-gradient-to-br from-violet-100 to-brand-100 dark:from-slate-900 dark:to-brand-950">
        @if ($post->thumbnail)
            <img src="{{ media_url($post->thumbnail) }}" alt="{{ $post->title }}" loading="lazy" width="640" height="360" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="grid size-full place-items-center"><x-icon name="heroicon-o-document-text" class="size-14 text-brand-400/60" /></div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
            @if ($post->category)
                <a href="{{ route('blog.category', $post->category) }}" class="font-semibold text-brand-600 dark:text-brand-400">{{ $post->category->name }}</a>
                <span aria-hidden="true">•</span>
            @endif
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('M d, Y') }}</time>
            <span aria-hidden="true">•</span>
            <span>{{ $post->readingTime() }} min read</span>
        </div>
        <h3 class="mt-2 text-lg leading-snug font-bold"><a href="{{ $post->url() }}" class="hover:text-brand-600 dark:hover:text-brand-300">{{ $post->title }}</a></h3>
        <p class="mt-2 line-clamp-3 text-sm text-slate-600 dark:text-slate-400">{{ $post->excerptText() }}</p>
    </div>
</article>
