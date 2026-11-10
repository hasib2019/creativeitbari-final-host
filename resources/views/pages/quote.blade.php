<x-layouts.app title="Get a Free Quote" description="Tell us about your software, web application, e-commerce or mobile app project and get a free quote within 24 hours.">
    <x-page-hero title="Start your <span class='gradient-text'>project</span>" subtitle="Share a few details — we'll come back within 24 hours with a plan, timeline and a transparent quote." eyebrow="Free Quote" :breadcrumbs="['Get a Quote' => null]" />

    <section class="section">
        <div class="container-x grid gap-10 lg:grid-cols-5">
            <div class="card p-6 sm:p-8 lg:col-span-3">
                <form action="{{ route('inquiries.store') }}" method="POST">
                    <input type="hidden" name="type" value="quote">
                    <x-inquiry-fields message-label="Tell us about your project">
                        <div>
                            <label for="f-item" class="label">I'm interested in</label>
                            <select id="f-item" name="item_name" class="input">
                                <option value="">Select a service…</option>
                                @foreach ($services as $s)
                                    <option @selected(old('item_name', $selected) === $s)>{{ $s }}</option>
                                @endforeach
                                @if ($selected && ! $services->contains($selected))
                                    <option selected>{{ $selected }}</option>
                                @endif
                                <option @selected(old('item_name') === 'Other')>Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="f-budget" class="label">Estimated budget</label>
                            <select id="f-budget" name="budget" class="input">
                                <option value="">Select budget…</option>
                                @foreach ($budgets as $b)
                                    <option @selected(old('budget') === $b)>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </x-inquiry-fields>
                    <button class="btn-primary mt-6 w-full sm:w-auto">Request Quote <x-icon name="heroicon-m-arrow-right" class="size-4" /></button>
                </form>
            </div>
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-6">
                    <h2 class="text-lg font-bold">What happens next?</h2>
                    <ol class="mt-5 space-y-5">
                        @foreach (['We review your requirements' => 'A project lead reads your brief, usually the same day.', 'Free consultation call' => 'We clarify scope, suggest the best approach and answer your questions.', 'Proposal & quote' => 'You receive a clear timeline, milestones and fixed price.'] as $title => $text)
                            <li class="flex gap-4">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                                <div><p class="font-semibold text-slate-900 dark:text-white">{{ $title }}</p><p class="text-sm text-slate-600 dark:text-slate-400">{{ $text }}</p></div>
                            </li>
                        @endforeach
                    </ol>
                </div>
                <x-contact-info />
            </div>
        </div>
    </section>
</x-layouts.app>
