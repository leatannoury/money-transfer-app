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

    // Mark notifications read
    $agent->agentNotifications()
        ->where('is_read', false)
        ->update(['is_read' => true]);

    // 1) User ↔ User transfers (anything that is NOT cash_in / cash_out)
    $userTransfers = Transaction::with(['sender', 'receiver'])
        ->where('agent_id', $agent->id)
        ->whereNotIn('service_type', ['cash_in', 'cash_out'])
        ->orderBy('created_at', 'desc')
        ->paginate(10, ['*'], 'transfers_page');

    // 2) Cash-In
    $cashIns = Transaction::with(['sender', 'receiver'])
        ->where('agent_id', $agent->id)
        ->where('service_type', 'cash_in')
        ->orderBy('created_at', 'desc')
        ->paginate(10, ['*'], 'cash_in_page');

    // 3) Cash-Out
    $cashOuts = Transaction::with(['sender', 'receiver'])
        ->where('agent_id', $agent->id)
        ->where('service_type', 'cash_out')
        ->orderBy('created_at', 'desc')
        ->paginate(10, ['*'], 'cash_out_page');

    return view('agent.transactions', compact(
        'agent',
        'userTransfers',
        'cashIns',
        'cashOuts'
    ));
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
    $agent = auth()->user();

    if ($transaction->agent_id !== $agent->id || $transaction->status !== 'in_progress') {
        return back()->with('error', 'Unauthorized or invalid transaction.');
    }

    $sender = User::find($transaction->sender_id);
    $receiver = User::find($transaction->receiver_id);

    $transactionCurrency = $transaction->currency ?? 'USD';
    if ($transactionCurrency === 'USD') {
        $amountInUsd = $transaction->amount;
    } else {
        $amountInUsd = round(CurrencyService::convert($transaction->amount, 'USD', $transactionCurrency), 2);
        
        if ($amountInUsd > ($transaction->amount * 100) && $transaction->amount > 100) {
            \Log::warning("Suspicious currency conversion: {$transaction->amount} {$transactionCurrency} = {$amountInUsd} USD");
            return back()->with('error', "Currency conversion failed. Please try again or contact support.");
        }
    }

    $commissionRate = $agent->commission;
    $commissionAmount = ($amountInUsd * $commissionRate) / 100;

    if ($transaction->service_type === 'cash_pickup') {
        // ✅ FIX: For cash pickup, money was ALREADY deducted from sender
        // Agent just confirms they handed the cash to recipient
        // Only add commission to agent
        
        $agent->balance += $commissionAmount;
        $agent->save();

        $transaction->status = 'completed';
        $transaction->amount_usd = $amountInUsd;
        $transaction->fee_percent = $commissionRate;
        $transaction->fee_amount_usd = $commissionAmount;
        $transaction->save();

        NotificationService::transferCompleted($transaction);

        return back()->with('success', 'Cash pickup completed successfully. Commission credited to your account.');
        
    } else {
        // ✅ Regular transfer_via_agent logic - deduct from sender NOW
        $receiverAmount = $amountInUsd - $commissionAmount;

        if ($sender->balance < $amountInUsd) {
            return back()->with('error', 'Sender does not have enough balance.');
        }

        $sender->balance -= $amountInUsd;
        $receiver->balance += $receiverAmount;
        $agent->balance += $commissionAmount;

        $sender->save();
        $receiver->save();
        $agent->save();

        $transaction->status = 'completed';
        $transaction->amount_usd = $amountInUsd;
        $transaction->fee_percent = $commissionRate;
        $transaction->fee_amount_usd = $commissionAmount;
        $transaction->save();

        NotificationService::transferCompleted($transaction);

        return back()->with('success', 'Transaction completed successfully. Commission credited.');
    }
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

    $transaction = Transaction::create([
        'sender_id'    => $agent->id,
        'receiver_id'  => $user->id,
        'amount'       => $amount,
        'amount_usd'   => $amount,
        'currency'     => 'USD',
        'service_type' => 'cash_in',
        'agent_id'     => $agent->id,
        'status'       => 'completed',
        'fee_percent'  => 0.5,
        'fee_amount_usd' => $commission,
    ]);

    NotificationService::sendAgentNotification(
        $agent,
        'Cash-In Completed',
        "You loaded " . CurrencyService::format($amount, 'USD') . " for {$user->name}.",
        $transaction
    );

    NotificationService::sendUserNotification(
        $user,
        'cash_in',
        'Cash-In Completed',
        "Agent {$agent->name} added " . CurrencyService::format($amount, 'USD') . " to your wallet (fee " . CurrencyService::format($commission, 'USD') . ").",
        $transaction
    );

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

    $transaction = Transaction::create([
        'sender_id'    => $user->id,
        'receiver_id'  => $agent->id,
        'amount'       => $amount,
        'amount_usd'   => $amount,
        'currency'     => 'USD',
        'service_type' => 'cash_out',
        'agent_id'     => $agent->id,
        'status'       => 'completed',
        'fee_percent'  => 0.5,
        'fee_amount_usd' => $commission,
    ]);

    NotificationService::sendAgentNotification(
        $agent,
        'Cash-Out Completed',
        "You handed " . CurrencyService::format($amount, 'USD') . " to {$user->name}.",
        $transaction
    );

    NotificationService::sendUserNotification(
        $user,
        'cash_out',
        'Cash-Out Completed',
        "Agent {$agent->name} processed your cash-out of " . CurrencyService::format($amount, 'USD') . " (fee " . CurrencyService::format($commission, 'USD') . ").",
        $transaction
    );

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
