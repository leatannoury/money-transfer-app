<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\FakeBankAccount;
use App\Models\FakeCard;

class SuspiciousController extends Controller
{
    // Show suspicious transactions
    public function suspiciousTransactions()
    {
        $transactions = Transaction::with(['sender', 'receiver'])
            ->where('status', 'suspicious')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.ManageTransaction.suspicious', compact('transactions'));
    }

    // Accept suspicious transaction
    public function acceptSuspicious($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'suspicious') {
            return back()->with('error', 'This transaction is no longer suspicious.');
        }

        $sender = $transaction->sender;
        $receiver = $transaction->receiver;
        $admin = auth()->user(); // the logged-in admin
        $fee = round(($transaction->amount * ($admin->commission ?? 0)) / 100, 2);

        // Deduct sender balance
        if ($transaction->payment_method === 'wallet') {
            $sender->balance -= $transaction->amount;
        } elseif ($transaction->payment_method === 'credit_card') {
            $card = FakeCard::where('card_number', 'like', '%'.$transaction->sender->last4)->first();
            $card->balance -= $transaction->amount;
            $card->save();
        } elseif ($transaction->payment_method === 'bank_account') {
            $bank = FakeBankAccount::where('account_number', 'like', '%'.$transaction->sender->last4)->first();
            $bank->balance -= $transaction->amount;
            $bank->save();
        }
        $sender->save();

        // Add receiver balance minus fee
        $receiver->balance += $transaction->amount - $fee;
        $receiver->save();

        // Admin fee
        $admin->balance += $fee;
        $admin->save();

        $transaction->status = 'completed';
        $transaction->save();

        return back()->with('success', 'Suspicious transaction accepted and balances updated.');
    }

    // Reject suspicious transaction
    public function rejectSuspicious($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'suspicious') {
            return back()->with('error', 'This transaction is no longer suspicious.');
        }

        $transaction->status = 'failed';
        $transaction->save();

        return back()->with('success', 'Suspicious transaction rejected.');
    }
}
