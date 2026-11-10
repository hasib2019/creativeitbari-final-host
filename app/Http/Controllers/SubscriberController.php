<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return back()->with('subscribed', 'Thanks for subscribing!');
        }

        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email:rfc', 'max:150'],
        ]);

        Subscriber::updateOrCreate(['email' => strtolower($data['email'])], ['is_active' => true]);

        return back()->with('subscribed', 'Thanks for subscribing! You will hear from us soon.');
    }
}
