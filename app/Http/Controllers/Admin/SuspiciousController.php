<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\FakeBankAccount;
use App\Models\FakeCard;
use App\Services\NotificationService;

class SuspiciousController extends Controller
{
    /**
     * Show all suspicious transactions
     */
    public function suspiciousTransactions()
    {
        $transactions = Transaction::with(['sender', 'receiver'])
            ->where('status', 'suspicious')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.ManageTransaction.suspicious', compact('transactions'));
    }

    /**
     * Accept suspicious transaction
     */
    public function acceptSuspicious($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'suspicious') {
            return back()->with('error', 'This transaction is no longer suspicious.');
        }

        $sender = $transaction->sender;
        $receiver = $transaction->receiver;
        $admin = auth()->user(); // logged-in admin
        $fee = round(($transaction->amount * ($admin->commission ?? 0)) / 100, 2);

        // ---------------------------
        // Check sender's balance
        // ---------------------------
        if ($transaction->payment_method === 'wallet' && $sender->balance < $transaction->amount) {
            return back()->with('error', 'Sender does not have enough wallet balance.');
        }

        if ($transaction->payment_method === 'credit_card') {
            $card = FakeCard::where('card_number', 'like', '%'.$transaction->sender->last4)->first();
            if (!$card || $card->balance < $transaction->amount) {
                return back()->with('error', 'Sender credit card does not have enough balance.');
            }
        }

        if ($transaction->payment_method === 'bank_account') {
            $bank = FakeBankAccount::where('account_number', 'like', '%'.$transaction->sender->last4)->first();
            if (!$bank || $bank->balance < $transaction->amount) {
                return back()->with('error', 'Sender bank account does not have enough balance.');
            }
        }

        // ---------------------------
        // Deduct balances
        // ---------------------------
        if ($transaction->payment_method === 'wallet') {
            $sender->balance -= $transaction->amount;
        } elseif ($transaction->payment_method === 'credit_card') {
            $card->balance -= $transaction->amount;
            $card->save();
        } elseif ($transaction->payment_method === 'bank_account') {
            $bank->balance -= $transaction->amount;
            $bank->save();
        }
        $sender->save();

        // Add receiver balance minus admin fee
        $receiver->balance += $transaction->amount - $fee;
        $receiver->save();

        // Admin receives the fee
        $admin->balance += $fee;
        $admin->save();

        // Mark transaction completed
        $transaction->status = 'completed';
        $transaction->save();

        NotificationService::transferCompleted($transaction);
        NotificationService::notifyAdmins(
            'Suspicious Transaction Approved',
            "Transaction #{$transaction->id} was approved after review.",
            $transaction
        );

        return back()->with('success', 'Suspicious transaction accepted and balances updated.');
    }

    /**
     * Reject suspicious transaction
     */
    public function rejectSuspicious($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'suspicious') {
            return back()->with('error', 'This transaction is no longer suspicious.');
        }

        $transaction->status = 'failed';
        $transaction->save();

        NotificationService::transferFailed($transaction, 'Rejected by admin');
        NotificationService::notifyAdmins(
            'Suspicious Transaction Rejected',
            "Transaction #{$transaction->id} was rejected after review.",
            $transaction
        );

        return back()->with('success', 'Suspicious transaction rejected.');
    }
}
