<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Services\Eps\EpsException;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * The hosted checkout: an app sends its customer to /pay/{reference}, the
 * customer confirms their details, pays at EPS and is sent back to the app.
 */
class CheckoutController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function show(Payment $payment): View|RedirectResponse
    {
        $payment->load('merchantApp');

        // Came back from EPS another way (back button, closed tab): check the open attempt.
        if ($payment->status === 'processing') {
            $payment = $this->payments->refresh($payment)->load('merchantApp');
        }

        if ($payment->isPaid()) {
            return view('pages.checkout.done', [
                'payment' => $payment,
                'returnUrl' => $this->payments->returnUrl($payment),
            ]);
        }

        if (! $payment->canBePaid()) {
            return view('pages.checkout.done', [
                'payment' => $payment,
                'returnUrl' => $this->payments->returnUrl($payment, cancelled: true),
            ]);
        }

        return view('pages.checkout.show', ['payment' => $payment]);
    }

    public function pay(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'string', 'min:6', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:60'],
        ]);

        try {
            $attempt = $this->payments->start($payment, $data, $request->ip());
        } catch (PaymentException $e) {
            return redirect()->route('checkout.show', $payment)->with('checkout_error', $e->getMessage());
        } catch (EpsException $e) {
            return redirect()->route('checkout.show', $payment)->withInput()
                ->with('checkout_error', 'The payment gateway could not start this payment. Please try again in a moment.')
                ->with('checkout_error_detail', app()->hasDebugModeEnabled() ? $e->getMessage() : null);
        }

        return redirect()->away($attempt->redirect_url);
    }

    /** EPS sends the customer here (successUrl / failUrl / cancelUrl). */
    public function epsReturn(Request $request, string $attempt, string $result): View|RedirectResponse
    {
        $attempt = PaymentAttempt::with('payment.merchantApp')->where('merchant_transaction_id', $attempt)->firstOrFail();
        $payment = $attempt->payment;

        try {
            $payment = $this->payments->verify($attempt, $result);
        } catch (EpsException $e) {
            Log::warning('EPS verify on return failed', ['payment' => $payment->reference, 'error' => $e->getMessage()]);

            return redirect()->route('checkout.show', $payment)
                ->with('checkout_notice', 'We could not confirm the payment with the bank yet. If money was taken, this page will update shortly — please do not pay again.');
        }

        if ($payment->isPaid()) {
            return redirect()->away($this->payments->returnUrl($payment));
        }

        if ($payment->status === 'processing') {
            return redirect()->route('checkout.show', $payment)
                ->with('checkout_notice', 'Your payment is being confirmed. If money was taken, please wait a minute and press "Check again" — do not pay twice.');
        }

        return redirect()->route('checkout.show', $payment)->with('checkout_error', match ($payment->status) {
            'cancelled' => 'The payment was cancelled. You can try again or go back.',
            default => 'The payment was not completed'.($payment->failure_reason ? ' ('.$payment->failure_reason.')' : '').'. You can try again with another method.',
        });
    }

    /** "Cancel and go back" on the checkout page. */
    public function cancel(Payment $payment): RedirectResponse
    {
        $payment = $this->payments->cancel($payment);

        return redirect()->away($this->payments->returnUrl($payment, cancelled: ! $payment->isPaid()));
    }
}
