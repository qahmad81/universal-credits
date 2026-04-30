<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentRequest;
use App\Services\UCMask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class TopupController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();
        return view('client.topup.index', compact('paymentMethods'));
    }

    public function manual()
    {
        $method = PaymentMethod::where('slug', 'manual_transfer')->firstOrFail();
        $config = $method->config;
        return view('client.topup.manual', compact('method', 'config'));
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:100',
            'reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $method = PaymentMethod::where('slug', 'manual_transfer')->firstOrFail();

        PaymentRequest::create([
            'user_id' => auth()->id(),
            'payment_method_id' => $method->id,
            'amount' => UCMask::toDb($request->amount),
            'reference' => $request->reference,
            'user_notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('topup.index')->with('status', 'Payment request submitted for review');
    }

    public function stripe()
    {
        $method = PaymentMethod::where('slug', 'stripe')->firstOrFail();
        $keysConfigured = !empty(config('services.stripe.key')) && !empty(config('services.stripe.secret'));
        return view('client.topup.stripe', compact('method', 'keysConfigured'));
    }

    public function storeStripe(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:100',
        ]);

        if (empty(config('services.stripe.secret'))) {
            return back()->withErrors(['stripe' => 'Stripe not configured']);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $amountUc = $request->amount;
        $amountDollars = UCMask::ucToDollars($amountUc);
        $amountCents = (int) ($amountDollars * 100);

        if ($amountCents < 50) {
            return back()->withErrors(['amount' => 'Minimum amount is 50 cents (5000 UC)']);
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Universal Credits Top-up',
                    ],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('topup.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('topup.stripe.cancel'),
            'metadata' => [
                'user_id' => auth()->id(),
                'amount_uc' => $amountUc,
            ],
        ]);

        return redirect($session->url);
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');
        if (!$sessionId) return redirect()->route('topup.index');

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            $userId = $session->metadata->user_id;
            $amountUc = $session->metadata->amount_uc;
            
            // Check if already processed
            $exists = PaymentRequest::where('meta->stripe_session_id', $sessionId)->exists();
            if ($exists) {
                return redirect()->route('topup.index')->with('status', 'Payment already processed');
            }

            $user = \App\Models\User::find($userId);
            $method = PaymentMethod::where('slug', 'stripe')->first();

            $dbAmount = UCMask::toDb($amountUc);

            $paymentRequest = PaymentRequest::create([
                'user_id' => $userId,
                'payment_method_id' => $method->id,
                'amount' => $dbAmount,
                'reference' => $session->payment_intent,
                'status' => 'approved',
                'meta' => ['stripe_session_id' => $sessionId],
            ]);

            // Credit balance
            $balance = $user->clientBalance;
            $balanceBefore = $balance->final_balance;
            
            $balance->final_balance += $dbAmount;
            $balance->pending_balance += $dbAmount;
            $balance->save();

            \App\Models\Transaction::create([
                'user_id' => $userId,
                'type' => 'topup',
                'amount' => $dbAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balance->final_balance,
                'description' => "Stripe Top-up: {$amountUc} UC",
                'reference_id' => $paymentRequest->id,
                'created_at' => now(),
            ]);

            if ($user->phone) {
                Log::info("WhatsApp notification: User {$user->name} topped up " . number_format($amountUc) . " UC");
            }

            return redirect()->route('topup.index')->with('status', 'Payment successful! Your balance has been updated.');
        }

        return redirect()->route('topup.index')->withErrors(['stripe' => 'Payment verification failed.']);
    }

    public function stripeCancel()
    {
        return redirect()->route('topup.index')->withErrors(['stripe' => 'Payment was cancelled.']);
    }

    public function crypto()
    {
        return view('client.topup.crypto');
    }
}
