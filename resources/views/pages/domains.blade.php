<x-layouts.app title="Domain Registration in Bangladesh" description="Register .com, .net, .com.bd, .xyz and 10+ domain extensions at the best price in BDT. Instant search, free DNS management and easy transfer." :noindex="$query !== ''">
    <x-page-hero title="Find your perfect <span class='gradient-text'>domain name</span>" subtitle="Instant search across popular extensions. Free DNS management, domain forwarding and easy transfers." eyebrow="Domains" :breadcrumbs="['Domains' => null]">
        <form method="GET" action="{{ route('domains.index') }}" class="mx-auto mt-8 flex max-w-2xl items-center gap-2 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-brand-900/5 dark:border-white/10 dark:bg-ink-900" role="search">
            <x-icon name="heroicon-o-globe-alt" class="ml-3 size-5 shrink-0 text-slate-400" />
            <input name="domain" value="{{ $query }}" placeholder="Type your business name, e.g. mybrand" autocomplete="off" required class="min-w-0 flex-1 bg-transparent px-1 py-3 text-base focus:outline-none dark:text-white" aria-label="Domain name">
            <button class="btn-primary px-6">Search</button>
        </form>
    </x-page-hero>

    @if ($query !== '')
        <section class="section pt-10">
            <div class="container-x max-w-4xl">
                @if (! $name)
                    <div class="card p-6 text-center text-rose-600">Please enter a valid domain name (letters, numbers and hyphens only).</div>
                @elseif ($results->isEmpty())
                    <div class="card p-6 text-center">No extensions are configured yet.</div>
                @else
                    <h2 class="text-xl font-bold">Results for “{{ $name }}”</h2>
                    <ul class="mt-6 space-y-3">
                        @foreach ($results as $r)
                            <li @class(['card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between', 'border-emerald-300 dark:border-emerald-500/30' => $r['available'] && $loop->first])>
                                <div class="flex items-center gap-3">
                                    <span @class(['grid size-10 place-items-center rounded-full', 'bg-emerald-500/15 text-emerald-600' => $r['available'], 'bg-rose-500/10 text-rose-500' => ! $r['available']])>
                                        <x-icon :name="$r['available'] ? 'heroicon-o-check' : 'heroicon-o-x-mark'" class="size-5" />
                                    </span>
                                    <div>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $r['domain'] }}</p>
                                        <p @class(['text-sm', 'text-emerald-600 dark:text-emerald-400' => $r['available'], 'text-rose-500' => ! $r['available']])>{{ $r['available'] ? 'Available' : 'Already registered' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    @if ($r['available'])
                                        <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ money($r['tld']->register_price) }}<span class="text-sm font-normal text-slate-500">/yr</span></span>
                                        <a href="{{ route('order', ['domain' => $r['domain']]) }}" class="btn-primary">Register</a>
                                    @else
                                        <a href="{{ route('order', ['domain' => $r['domain'], 'action' => 'transfer']) }}" class="btn-outline">Transfer it</a>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-slate-500">Availability is a quick live estimate. Final availability is confirmed with the registry when you order.</p>
                @endif
            </div>
        </section>
    @endif

    <section @class(['section', 'section-alt' => $query !== ''])>
        <div class="container-x">
            <x-section-heading eyebrow="Pricing" title="Domain <span class='gradient-text'>price list</span>" subtitle="Transparent prices in BDT. Free DNS management with every domain." />
            <div class="card mx-auto mt-12 max-w-4xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs tracking-wider text-slate-500 uppercase dark:bg-white/5 dark:text-slate-400">
                            <tr><th class="px-6 py-4">Extension</th><th class="px-6 py-4">Register</th><th class="px-6 py-4">Renew</th><th class="px-6 py-4">Transfer</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($tlds as $tld)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-white/[0.03]">
                                    <td class="px-6 py-4"><span class="text-base font-bold text-slate-900 dark:text-white">{{ $tld->tld }}</span> @if ($tld->is_popular)<span class="badge-brand ml-2 py-0.5 text-[10px]">Popular</span>@endif</td>
                                    <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">{{ money($tld->register_price) }}</td>
                                    <td class="px-6 py-4">{{ $tld->renew_price ? money($tld->renew_price) : '—' }}</td>
                                    <td class="px-6 py-4">{{ $tld->transfer_price ? money($tld->transfer_price) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <x-cta title="Need hosting for your new domain?" subtitle="Get fast NVMe hosting with free SSL — your website live today." button-text="View Hosting Plans" :button-url="route('hosting')" />
</x-layouts.app>
