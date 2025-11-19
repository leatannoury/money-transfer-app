<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\Transaction;
use App\Services\NotificationService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $prefillTransactionId = $request->integer('transaction_id');

        $requests = RefundRequest::with(['transaction.sender', 'transaction.receiver'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $eligibleTransactions = Transaction::with(['receiver'])
            ->where('sender_id', $user->id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('service_type', 'wallet_to_wallet')
                        ->where('status', 'completed');
                })->orWhere(function ($q) {
                    $q->where('service_type', 'transfer_via_agent')
                        ->where('status', 'in_progress');
                });
            })
            ->orderByDesc('created_at')
            ->take(25)
            ->get();

        if ($prefillTransactionId) {
            $alreadyListed = $eligibleTransactions->firstWhere('id', $prefillTransactionId);

            if (!$alreadyListed) {
                $prefillTransaction = Transaction::with('receiver')
                    ->where('sender_id', $user->id)
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('service_type', 'wallet_to_wallet')
                                ->where('status', 'completed');
                        })->orWhere(function ($q) {
                            $q->where('service_type', 'transfer_via_agent')
                                ->where('status', 'in_progress');
                        });
                    })
                    ->find($prefillTransactionId);

                if ($prefillTransaction) {
                    $eligibleTransactions->prepend($prefillTransaction);
                    $eligibleTransactions = $eligibleTransactions->unique('id')->values();
                }
            }
        }

        return view('user.refunds', compact('requests', 'eligibleTransactions', 'user', 'prefillTransactionId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        $transaction = Transaction::with(['sender', 'receiver', 'refundRequests'])
            ->findOrFail($data['transaction_id']);

        if ($transaction->sender_id !== $user->id) {
            return back()->with('error', 'You can only request changes for transactions you initiated.');
        }

        if (in_array($transaction->status, ['refunded'], true)) {
            return back()->with('error', 'This transaction has already been refunded.');
        }

        if ($transaction->refundRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', 'A pending request already exists for this transaction.');
        }

        $isWalletToWallet = $transaction->service_type === 'wallet_to_wallet' && $transaction->status === 'completed';
        $isWalletToPerson = $transaction->service_type === 'transfer_via_agent' && $transaction->status === 'in_progress';

        if (!$isWalletToWallet && !$isWalletToPerson) {
            return back()->with('error', 'This transaction is not eligible for a refund under the current rules.');
        }

        try {
            RefundService::assertRefundable($transaction);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $amounts = RefundService::calculateAmounts($transaction);

        $refundRequest = RefundRequest::create([
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'type' => 'refund',
            'reason' => $data['reason'],
            'requested_amount' => $amounts['net_currency'],
            'requested_amount_usd' => $amounts['net_usd'],
            'currency' => $amounts['currency'],
            'status' => 'pending',
        ]);

        $transaction->update(['status' => 'disputed']);

        NotificationService::refundRequestSubmitted($transaction, $user, 'refund');

        return redirect()
            ->route('user.refunds.index')
            ->with('success', 'Your request has been submitted. We will notify you once it is reviewed.');
    }
}

