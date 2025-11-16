<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ManageTransactionController extends Controller
{

public function manageTransaction(Request $request)
{
    $walletToWalletQuery = Transaction::with(['sender', 'receiver'])
        ->whereNull('agent_id');

    // Email filter (sender or receiver)
    if ($request->filled('wallet_email')) {
        $email = $request->wallet_email;
        $walletToWalletQuery->where(function ($q) use ($email) {
            $q->whereHas('sender', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            })->orWhereHas('receiver', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            });
        });
    }

    // Status filter
    if ($request->filled('wallet_status')) {
        $walletToWalletQuery->where('status', $request->wallet_status);
    }

    // Date range filters
    if ($request->filled('wallet_from_date')) {
        $walletToWalletQuery->whereDate('created_at', '>=', $request->wallet_from_date);
    }

    if ($request->filled('wallet_to_date')) {
        $walletToWalletQuery->whereDate('created_at', '<=', $request->wallet_to_date);
    }

    // Sorting
    if ($request->filled('wallet_sort')) {
        switch ($request->wallet_sort) {
            case 'oldest':
                $walletToWalletQuery->orderBy('created_at', 'asc');
                break;
            case 'amount_desc':
                $walletToWalletQuery->orderBy('amount', 'desc');
                break;
            case 'amount_asc':
                $walletToWalletQuery->orderBy('amount', 'asc');
                break;
            default:
                $walletToWalletQuery->orderBy('created_at', 'desc');
        }
    } else {
        $walletToWalletQuery->orderBy('created_at', 'desc');
    }

        $walletToWallet = $walletToWalletQuery->paginate(10)->withQueryString();

    // ================================
    // 🔹 WALLET TO PERSON TRANSACTIONS
    // ================================
    $walletToPersonQuery = Transaction::with(['sender', 'receiver', 'agent'])
        ->whereNotNull('agent_id');

    // Email filter (sender, receiver, or agent)
    if ($request->filled('person_email')) {
        $email = $request->person_email;
        $walletToPersonQuery->where(function ($q) use ($email) {
            $q->whereHas('sender', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            })->orWhereHas('receiver', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            })->orWhereHas('agent', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            });
        });
    }

    // Status filter
    if ($request->filled('person_status')) {
        $walletToPersonQuery->where('status', $request->person_status);
    }

    // Date range filters
    if ($request->filled('person_from_date')) {
        $walletToPersonQuery->whereDate('created_at', '>=', $request->person_from_date);
    }

    if ($request->filled('person_to_date')) {
        $walletToPersonQuery->whereDate('created_at', '<=', $request->person_to_date);
    }

    // Sorting
    if ($request->filled('person_sort')) {
        switch ($request->person_sort) {
            case 'oldest':
                $walletToPersonQuery->orderBy('created_at', 'asc');
                break;
            case 'amount_desc':
                $walletToPersonQuery->orderBy('amount', 'desc');
                break;
            case 'amount_asc':
                $walletToPersonQuery->orderBy('amount', 'asc');
                break;
            default:
                $walletToPersonQuery->orderBy('created_at', 'desc');
        }
    } else {
        $walletToPersonQuery->orderBy('created_at', 'desc');
    }

    $walletToPerson = $walletToPersonQuery->paginate(10)->withQueryString();
     

    // Return to view
    return view('admin.ManageTransaction.manageTransaction', compact('walletToWallet', 'walletToPerson'));
}

// Show suspicious transactions
public function suspiciousTransactions(Request $request)
{
    $walletToWalletQuery = Transaction::where('status', 'suspicious')
                    ->with(['sender', 'receiver', 'agent']);
                    
     if ($request->filled('wallet_email')) {
        $email = $request->wallet_email;
        $walletToWalletQuery->where(function ($q) use ($email) {
            $q->whereHas('sender', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            })->orWhereHas('receiver', function ($q2) use ($email) {
                $q2->where('email', 'like', "%{$email}%");
            });
        });
    }

    // Status filter
    if ($request->filled('wallet_status')) {
        $walletToWalletQuery->where('status', $request->wallet_status);
    }

    // Date range filters
    if ($request->filled('wallet_from_date')) {
        $walletToWalletQuery->whereDate('created_at', '>=', $request->wallet_from_date);
    }

    if ($request->filled('wallet_to_date')) {
        $walletToWalletQuery->whereDate('created_at', '<=', $request->wallet_to_date);
    }

    // Sorting
    if ($request->filled('wallet_sort')) {
        switch ($request->wallet_sort) {
            case 'oldest':
                $walletToWalletQuery->orderBy('created_at', 'asc');
                break;
            case 'amount_desc':
                $walletToWalletQuery->orderBy('amount', 'desc');
                break;
            case 'amount_asc':
                $walletToWalletQuery->orderBy('amount', 'asc');
                break;
            default:
                $walletToWalletQuery->orderBy('created_at', 'desc');
        }
    } else {
        $walletToWalletQuery->orderBy('created_at', 'desc');
    }

        $transactions = $walletToWalletQuery->paginate(10)->withQueryString();

    return view('admin.ManageTransaction.suspicious', compact('transactions'));
}

// Update status (complete/failed)
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:completed,failed'
    ]);

    $transaction = Transaction::findOrFail($id);
    $transaction->status = $request->status;
    $transaction->save();

    return redirect()->back()->with('success', 'Transaction status updated successfully.');
}


}
