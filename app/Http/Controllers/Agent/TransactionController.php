<?php

namespace App\Http\Controllers\Agent;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use App\Services\CurrencyService;
use App\Services\NotificationService;

class TransactionController extends Controller
{
    /**
     * Show all transactions assigned to this agent or pending ones
     */
    public function index()
    {
        $agent = Auth::user();

        $transactions = Transaction::with(['sender', 'receiver']) // 👈 add this
            ->where(function($query) use ($agent) {
                $query->where('status', 'pending_agent')
                      ->orWhere('agent_id', $agent->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.transactions', compact('transactions', 'agent'));
        $agent->agentNotifications()
    ->where('is_read', false)
    ->update(['is_read' => true]);
    }

    /**
     * Accept a pending transaction (assign to current agent)
     */
    public function accept($id)
    {
        $agent = Auth::user();
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending_agent') {
            return back()->with('error', 'This transaction is no longer available.');
        }

        $transaction->agent_id = $agent->id;
        $transaction->status = 'in_progress';
        $transaction->save();

        Log::info("Transaction #{$transaction->id} accepted by agent #{$agent->id}");

        return back()->with('success', 'Transaction accepted successfully.');
    }

    /**
     * Mark a transaction as completed and update balances
     */
    public function complete($id)
    {
        $transaction = Transaction::findOrFail($id);
        $agent = auth()->user(); // the logged-in agent

        // ✅ make sure this transaction belongs to this agent and is in progress
        if ($transaction->agent_id !== $agent->id || $transaction->status !== 'in_progress') {
            return back()->with('error', 'Unauthorized or invalid transaction.');
        }

        // ✅ fetch sender and receiver
        $sender   = User::find($transaction->sender_id);
        $receiver = User::find($transaction->receiver_id);

        // ✅ Convert transaction amount from transaction currency to USD
        // All balances are stored in USD, so we need to convert first
        $transactionCurrency = $transaction->currency ?? 'USD';
        if ($transactionCurrency === 'USD') {
            $amountInUsd = $transaction->amount;
        } else {
            $amountInUsd = round(CurrencyService::convert($transaction->amount, 'USD', $transactionCurrency), 2);
            
            // Validate conversion result - if it's suspiciously large, the conversion might have failed
            // (e.g., if API fails and defaults to 1.0, 20000 LBP would become 20000 USD)
            // Check if converted amount is more than 100x the original (indicates likely conversion error)
            if ($amountInUsd > ($transaction->amount * 100) && $transaction->amount > 100) {
                \Log::warning("Suspicious currency conversion: {$transaction->amount} {$transactionCurrency} = {$amountInUsd} USD");
                return back()->with('error', "Currency conversion failed. Please try again or contact support.");
            }
        }

        // ✅ agent commission rate (e.g., 10%)
        $commissionRate   = $agent->commission; // assuming it's stored as 10 for 10%
        $commissionAmount = ($amountInUsd * $commissionRate) / 100;

        // ✅ receiver gets the rest
        $receiverAmount = $amountInUsd - $commissionAmount;

        // ✅ update balances (all in USD)
        if ($sender->balance < $amountInUsd) {
            return back()->with('error', 'Sender does not have enough balance.');
        }

        $sender->balance   -= $amountInUsd;
        $receiver->balance += $receiverAmount;
        $agent->balance    += $commissionAmount;

        // ✅ mark transaction completed
        $transaction->status = 'completed';

        // ✅ save all changes
        $sender->save();
        $receiver->save();
        $agent->save();
        $transaction->save();

        NotificationService::transferCompleted($transaction);

        return back()->with('success', 'Transaction completed successfully. Commission credited.');
    }

    public function reject($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow if status is still in progress
        if ($transaction->status == 'in_progress') {
            $transaction->status = 'failed'; // ✅ add quotes
            $transaction->save();

            NotificationService::transferFailed($transaction, 'An agent rejected the transfer request.');
        }

        return redirect()->back()->with('success', 'Transaction rejected successfully.');
    }

    /* -----------------------------------------------------------------
     |  NEW: CASH-IN / CASH-OUT OPERATIONS
     |------------------------------------------------------------------*/

    /**
     * Show cash-in / cash-out form to the agent
     */
    public function cashForm()
    {
        $agent = Auth::user();
        return view('agent.cash', compact('agent'));
    }

    /**
     * Agent CASH-IN:
     * User gives cash, agent loads money into user wallet.
     */
public function cashIn(Request $request)
{
    $request->validate([
        'search_type'    => 'required|in:email,phone',
        'email_or_phone' => 'required|string',
        'amount'         => 'required|numeric|min:1',
    ]);

    // Lookup user safely
    $user = $request->search_type === 'email'
        ? User::where('email', $request->email_or_phone)->first()
        : User::where('phone', $request->email_or_phone)->first();

    if (!$user) {
        return back()->withErrors([
            'email_or_phone' => "No user found using this {$request->search_type}. Please double-check and try again.",
        ])->withInput();
    }

    $agent      = auth()->user();
    $amount     = $request->amount;
    $commission = $amount * 0.005; // 0.5%

 

    // Update balances
    $user->balance  = $user->balance - $commission + $amount;
    $agent->balance = $agent->balance + $commission;

    $user->save();
    $agent->save();

    Transaction::create([
        'sender_id'    => $agent->id,
        'receiver_id'  => $user->id,
        'amount'       => $amount,
        'currency'     => 'USD',
        'service_type' => 'cash_in',
        'agent_id'     => $agent->id,
        'status'       => 'completed',
    ]);

    return back()->with('success', "Cash-In completed successfully.");
}


public function cashOut(Request $request)
{
    $request->validate([
        'search_type'    => 'required|in:email,phone',
        'email_or_phone' => 'required|string',
        'amount'         => 'required|numeric|min:1',
    ]);

    // Lookup user safely
    $user = $request->search_type === 'email'
        ? User::where('email', $request->email_or_phone)->first()
        : User::where('phone', $request->email_or_phone)->first();

    if (!$user) {
        return back()->withErrors([
            'email_or_phone' => "No user found using this {$request->search_type}. Please enter a valid email or phone.",
        ])->withInput();
    }

    $agent      = auth()->user();
    $amount     = $request->amount;
    $commission = $amount * 0.005; // 0.5%
    $total      = $amount + $commission;

    if ($user->balance < $total) {
        return back()->withErrors([
            'amount' => "User doesn't have enough balance to cash out $amount USD.",
        ])->withInput();
    }

    $user->balance  -= $total;
    $agent->balance += $commission;

    $user->save();
    $agent->save();

    Transaction::create([
        'sender_id'    => $user->id,
        'receiver_id'  => $agent->id,
        'amount'       => $amount,
        'currency'     => 'USD',
        'service_type' => 'cash_out',
        'agent_id'     => $agent->id,
        'status'       => 'completed',
    ]);

    return back()->with('success', "Cash-Out completed successfully.");
}



public function cashMenu()
{
    $agent = Auth::user();
    return view('agent.cash-menu', compact('agent'));
}

public function cashInForm()
{
    $agent = Auth::user();
    return view('agent.cash-in', compact('agent'));
}

public function cashOutForm()
{
    $agent = Auth::user();
    return view('agent.cash-out', compact('agent'));
}



}
