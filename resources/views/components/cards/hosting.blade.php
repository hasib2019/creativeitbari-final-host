@props(['plan', 'cycle' => 'yearly'])
<article @class([
    'relative flex flex-col rounded-3xl border p-7 transition duration-300',
    'border-brand-500 bg-gradient-to-b from-brand-600 to-violet-700 text-white shadow-2xl shadow-brand-600/30 lg:scale-105' => $plan->is_popular,
    'border-slate-200 bg-white hover:border-brand-300 hover:shadow-xl dark:border-white/10 dark:bg-ink-900/60' => ! $plan->is_popular,
])>
    @if ($plan->is_popular)
        <span class="badge absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-amber-950 shadow">Most Popular</span>
    @endif
    <h3 @class(['text-lg font-bold', 'text-white' => $plan->is_popular])>{{ $plan->name }}</h3>
    <p @class(['mt-1 text-sm', 'text-indigo-100' => $plan->is_popular, 'text-slate-500 dark:text-slate-400' => ! $plan->is_popular])>{{ $plan->short_description }}</p>
    <div class="mt-6 flex items-baseline gap-1">
        @php $monthly = $plan->monthly_price; $yearly = $plan->yearly_price; @endphp
        <span class="text-4xl font-extrabold tracking-tight" x-text="cycle === 'monthly' ? @js(money($monthly)) : @js(money($yearly))">{{ money($cycle === 'monthly' ? $monthly : $yearly) }}</span>
        <span @class(['text-sm', 'text-indigo-100' => $plan->is_popular, 'text-slate-500' => ! $plan->is_popular]) x-text="cycle === 'monthly' ? '/month' : '/year'">/{{ $cycle === 'monthly' ? 'month' : 'year' }}</span>
    </div>
    <ul class="mt-6 space-y-3 text-sm">
        @foreach ($plan->features ?? [] as $feature)
            <li class="flex gap-2.5">
                <x-icon name="heroicon-s-check-circle" @class(['size-5 shrink-0', 'text-cyan-300' => $plan->is_popular, 'text-emerald-500' => ! $plan->is_popular]) />
                <span>{{ $feature }}</span>
            </li>
        @endforeach
    </ul>
    <div class="mt-auto pt-8">
        <a :href="@js($plan->order_url ?: route('order', ['hosting' => $plan->slug])) + (@js((bool) $plan->order_url) ? '' : '&cycle=' + cycle)"
           href="{{ $plan->order_url ?: route('order', ['hosting' => $plan->slug, 'cycle' => $cycle]) }}"
           @class(['btn w-full', 'bg-white text-brand-700 hover:bg-indigo-50' => $plan->is_popular, 'btn-primary' => ! $plan->is_popular])>
            Order Now
        </a>
    </div>
</article>
