<?php

namespace App\Http\Controllers;

use App\Mail\NewInquiryMail;
use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public const BUDGETS = [
        'Below ৳25,000',
        '৳25,000 – ৳75,000',
        '৳75,000 – ৳2,00,000',
        '৳2,00,000 – ৳5,00,000',
        'Above ৳5,00,000',
        'Not sure yet',
    ];

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function quote(Request $request): View
    {
        return view('pages.quote', [
            'services' => Service::active()->ordered()->pluck('title'),
            'budgets' => self::BUDGETS,
            'selected' => $request->query('service'),
        ]);
    }

    /** Order request form for a product plan, hosting plan or domain. */
    public function order(Request $request): View|RedirectResponse
    {
        $item = $this->resolveOrderItem($request);

        if (! $item) {
            return redirect()->route('products.index');
        }

        return view('pages.order', ['item' => $item]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: bots fill the hidden "website" field. Pretend success, store nothing.
        if (filled($request->input('website'))) {
            return redirect()->route('inquiries.thanks');
        }

        $type = $request->input('type', 'contact');
        $isOrder = in_array($type, ['order', 'domain', 'hosting'], true);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Inquiry::TYPES))],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => [$isOrder ? 'required' : 'nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'message' => [$isOrder ? 'nullable' : 'required', 'string', 'max:5000'],
        ]);

        $inquiry = Inquiry::create($data + ['ip_address' => $request->ip()]);

        if ($to = setting('email')) {
            try {
                Mail::to($to)->send(new NewInquiryMail($inquiry));
            } catch (\Throwable $e) {
                Log::warning('Inquiry notification email failed: '.$e->getMessage());
            }
        }

        return redirect()->route('inquiries.thanks')->with('inquiry_type', $inquiry->type);
    }

    public function thanks(): View
    {
        return view('pages.thanks', ['type' => session('inquiry_type', 'contact')]);
    }

    private function resolveOrderItem(Request $request): ?array
    {
        if ($slug = $request->query('product')) {
            $product = Product::active()->where('slug', $slug)->first();
            if (! $product) {
                return null;
            }

            $plan = collect($product->plans ?? [])->firstWhere('name', $request->query('plan'));
            $price = $plan['price'] ?? $product->effectivePrice();
            $period = $plan['period'] ?? null;

            return [
                'type' => 'order',
                'title' => $product->name.($plan ? ' — '.$plan['name'] : ''),
                'subtitle' => $product->typeLabel(),
                'price' => $price,
                'period' => $period,
                'url' => $product->url(),
            ];
        }

        if ($slug = $request->query('hosting')) {
            $plan = HostingPlan::active()->where('slug', $slug)->first();
            if (! $plan) {
                return null;
            }
            $cycle = $request->query('cycle') === 'monthly' ? 'monthly' : 'yearly';

            return [
                'type' => 'hosting',
                'title' => $plan->name.' Hosting ('.ucfirst($cycle).')',
                'subtitle' => HostingPlan::TYPES[$plan->type] ?? 'Hosting',
                'price' => $cycle === 'monthly' ? $plan->monthly_price : $plan->yearly_price,
                'period' => $cycle,
                'url' => route('hosting'),
            ];
        }

        if ($domain = $request->query('domain')) {
            $domain = strtolower(preg_replace('/[^a-zA-Z0-9.-]/', '', $domain));
            $tld = DomainPrice::active()->get()->first(fn ($t) => str_ends_with($domain, $t->tld));
            if (! $tld || $domain === $tld->tld) {
                return null;
            }

            $action = $request->query('action') === 'transfer' ? 'transfer' : 'register';

            return [
                'type' => 'domain',
                'title' => $domain.' ('.ucfirst($action).')',
                'subtitle' => 'Domain '.ucfirst($action),
                'price' => $action === 'transfer' ? ($tld->transfer_price ?: $tld->register_price) : $tld->register_price,
                'period' => 'yearly',
                'url' => route('domains.index'),
            ];
        }

        return null;
    }
}
