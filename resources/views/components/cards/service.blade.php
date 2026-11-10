@props(['service'])
<article class="card-hover group relative flex flex-col p-6 sm:p-7">
    <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-violet-600 text-white shadow-lg shadow-brand-600/25 transition group-hover:scale-110 group-hover:rotate-3">
        <x-icon :name="$service->icon" class="size-7" />
    </span>
    <h3 class="mt-6 text-xl font-bold"><a href="{{ $service->url() }}" class="after:absolute after:inset-0">{{ $service->title }}</a></h3>
    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $service->short_description }}</p>
    <span class="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-brand-600 dark:text-brand-300">
        Learn more <x-icon name="heroicon-m-arrow-right" class="size-4 transition group-hover:translate-x-1" />
    </span>
</article>
