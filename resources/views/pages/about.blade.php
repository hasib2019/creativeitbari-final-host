@php
    $stats = setting('home_stats', []);
    $socialIcons = ['facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'twitter' => 'X', 'github' => 'GitHub'];
@endphp
<x-layouts.app title="About Us" :description="strip_tags(setting('about_mission', 'Learn about our team, mission and the software, web and hosting solutions we build.'))" :image="setting('about_image')">
    <x-page-hero title="About <span class='gradient-text'>{{ setting('site_name', config('app.name')) }}</span>" :subtitle="setting('site_tagline')" eyebrow="Our Story" :breadcrumbs="['About Us' => null]" />

    <section class="section">
        <div class="container-x grid items-center gap-14 lg:grid-cols-2">
            <div class="relative">
                @if ($img = media_url(setting('about_image')))
                    <img src="{{ $img }}" alt="Our team" class="w-full rounded-3xl object-cover" width="720" height="560">
                @else
                    <div class="grid aspect-[4/3] place-items-center rounded-3xl bg-gradient-to-br from-brand-600 via-violet-600 to-cyan-500 p-10 text-white shadow-2xl shadow-brand-600/30">
                        <div class="text-center">
                            <x-icon name="heroicon-o-rocket-launch" class="mx-auto size-20 opacity-90" />
                            <p class="mt-6 text-2xl font-extrabold">Building digital Bangladesh, one product at a time.</p>
                        </div>
                    </div>
                @endif
                @if ($years = setting('about_experience_years'))
                    <div class="card absolute -right-3 -bottom-6 p-5 text-center shadow-xl sm:-right-6">
                        <p class="gradient-text text-4xl font-black">{{ $years }}+</p>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Years of experience</p>
                    </div>
                @endif
            </div>
            <div>
                <span class="badge-brand">Who we are</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ setting('about_title', 'We turn ideas into reliable software') }}</h2>
                <div class="prose prose-slate mt-6 max-w-none dark:prose-invert">{!! setting('about_content') !!}</div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @if ($mission = setting('about_mission'))
                        <div class="card p-5"><x-icon name="heroicon-o-flag" class="size-7 text-brand-500" /><h3 class="mt-3 font-bold">Our Mission</h3><p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $mission }}</p></div>
                    @endif
                    @if ($vision = setting('about_vision'))
                        <div class="card p-5"><x-icon name="heroicon-o-eye" class="size-7 text-brand-500" /><h3 class="mt-3 font-bold">Our Vision</h3><p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $vision }}</p></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($stats)
        <section class="py-12">
            <div class="container-x">
                <dl class="grid grid-cols-2 gap-6 rounded-3xl bg-gradient-to-r from-brand-700 via-violet-700 to-indigo-800 p-8 text-center text-white lg:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="flex flex-col-reverse"><dt class="mt-1 text-sm text-indigo-100">{{ $stat['label'] ?? '' }}</dt><dd class="text-4xl font-extrabold">{{ $stat['value'] ?? '' }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    @if ($team->isNotEmpty())
        <section class="section">
            <div class="container-x">
                <x-section-heading eyebrow="Our Team" title="The people behind <span class='gradient-text'>your product</span>" />
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($team as $member)
                        <div class="card-hover group overflow-hidden text-center">
                            <div class="aspect-square overflow-hidden bg-gradient-to-br from-brand-100 to-violet-100 dark:from-brand-950 dark:to-slate-900">
                                @if ($member->photo)
                                    <img src="{{ media_url($member->photo) }}" alt="{{ $member->name }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" width="400" height="400">
                                @else
                                    <div class="grid size-full place-items-center text-6xl font-black text-brand-400/70">{{ mb_substr($member->name, 0, 1) }}</div>
                                @endif
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold">{{ $member->name }}</h3>
                                <p class="text-sm text-brand-600 dark:text-brand-300">{{ $member->designation }}</p>
                                @if ($links = array_filter($member->social_links ?? []))
                                    <div class="mt-3 flex justify-center gap-3 text-xs font-semibold text-slate-500">
                                        @foreach ($links as $network => $url)
                                            <a href="{{ $url }}" target="_blank" rel="noopener" class="hover:text-brand-600">{{ $socialIcons[$network] ?? ucfirst($network) }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="section section-alt">
            <div class="container-x">
                <x-section-heading eyebrow="Testimonials" title="What clients say" />
                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @foreach ($testimonials->take(3) as $t)
                        <figure class="card p-6">
                            <div class="flex gap-0.5 text-amber-400">@for ($i = 0; $i < $t->rating; $i++)<x-icon name="heroicon-s-star" class="size-5" />@endfor</div>
                            <blockquote class="mt-4 text-sm leading-relaxed">“{{ $t->content }}”</blockquote>
                            <figcaption class="mt-5 text-sm"><strong class="text-slate-900 dark:text-white">{{ $t->name }}</strong><br><span class="text-slate-500">{{ collect([$t->designation, $t->company])->filter()->implode(', ') }}</span></figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta />
</x-layouts.app>
