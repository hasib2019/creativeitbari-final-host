<?php

namespace App\Http\Controllers;

use App\Models\DomainPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DomainController extends Controller
{
    /** How many extensions to check per search. */
    private const MAX_CHECKS = 8;

    public function index(Request $request): View
    {
        $tlds = DomainPrice::active()->orderByDesc('is_popular')->ordered()->get();
        $query = (string) $request->query('domain', '');
        $results = collect();
        $name = null;

        if ($query !== '') {
            [$name, $requestedTld] = $this->parse($query);

            if ($name) {
                $checkTlds = $tlds->sortByDesc(fn ($t) => $t->tld === $requestedTld)->take(self::MAX_CHECKS);

                $results = $checkTlds->map(fn (DomainPrice $tld) => [
                    'domain' => $name.$tld->tld,
                    'tld' => $tld,
                    'available' => ! $this->isRegistered($name.$tld->tld),
                ])->values();
            }
        }

        return view('pages.domains', [
            'tlds' => $tlds,
            'query' => $query,
            'name' => $name,
            'results' => $results,
        ]);
    }

    /**
     * "https://www.My-Shop.com/" → ["my-shop", ".com"]. Returns [null, null] for invalid input.
     */
    private function parse(string $input): array
    {
        $input = strtolower(trim($input));
        $input = preg_replace('#^https?://#', '', $input);
        $input = preg_replace('#^www\.#', '', $input);
        $input = explode('/', $input)[0];

        $parts = explode('.', $input, 2);
        $name = preg_replace('/[^a-z0-9-]/', '', $parts[0]);
        $name = trim($name, '-');
        $tld = isset($parts[1]) && $parts[1] !== '' ? '.'.$parts[1] : null;

        if ($name === '' || strlen($name) > 63) {
            return [null, null];
        }

        return [$name, $tld];
    }

    /**
     * Quick DNS-based check: a domain with NS/SOA records is registered.
     * This is a fast estimate; final availability is confirmed with the registrar on order.
     */
    private function isRegistered(string $domain): bool
    {
        return Cache::remember('domain_check:'.$domain, now()->addMinutes(15), function () use ($domain) {
            $fqdn = $domain.'.';

            return @checkdnsrr($fqdn, 'NS') || @checkdnsrr($fqdn, 'SOA') || @checkdnsrr($fqdn, 'A');
        });
    }
}
