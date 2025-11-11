<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Show all transactions assigned to this agent or pending ones
     */
    public function index()
    {
        $agent = Auth::user();

        // Agent can see transactions assigned to them OR pending ones
        $transactions = Transaction::where('status', 'pending')
            ->orWhere('agent_id', $agent->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.transactions', compact('transactions', 'agent'));
    }

    /**
     * Accept a pending transaction (assign to current agent)
     */
    public function accept($id)
    {
        $agent = Auth::user();
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
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
        $agent = Auth::user();
        $transaction = Transaction::where('id', $id)
            ->where('agent_id', $agent->id)
            ->firstOrFail();

        if ($transaction->status !== 'in_progress') {
            return back()->with('error', 'You can only complete transactions in progress.');
        }

        // Fetch sender and receiver
        $sender = $transaction->sender;
        $receiver = $transaction->receiver;

        if (!$sender || !$receiver) {
            return back()->with('error', 'Transaction sender or receiver not found.');
        }

        // Check sender balance
        if ($sender->balance < $transaction->amount) {
            return back()->with('error', 'Sender does not have enough balance.');
        }

        // ✅ Update balances
        $sender->balance -= $transaction->amount;
        $receiver->balance += $transaction->amount;

        $sender->save();
        $receiver->save();

        // ✅ Mark transaction as completed
        $transaction->status = 'completed';
        $transaction->save();

        Log::info("Transaction #{$transaction->id} completed by agent #{$agent->id}");

        return back()->with('success', 'Transaction completed successfully! Balances updated.');
    }
}
