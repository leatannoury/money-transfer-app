<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    // Show Add Funds Page
    public function showAddFunds()
    {
        return view('user.wallet.add-funds');
    }

    // Stripe Checkout session creation
    public function createCheckout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Wallet Funding',
                    ],
                    'unit_amount' => $request->amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('user.wallet.success') . '?session_id={CHECKOUT_SESSION_ID}&amount=' . $request->amount,
            'cancel_url' => route('user.wallet.cancel'),
        ]);

        return redirect($session->url);
    }

    // When payment succeeds
    public function success(Request $request)
    {
        $amount = $request->amount;

        $user = Auth::user();
        $user->balance += $amount;   // your users table uses "balance"
        $user->save();

        return view('user.wallet.success', compact('amount'));
    }

    // When cancelled
    public function cancel()
    {
        return view('user.wallet.cancel');
    }
}
