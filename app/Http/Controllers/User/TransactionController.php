<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Beneficiary;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('sender_id', Auth::id())
                                   ->orWhere('receiver_id', Auth::id())
                                   ->with(['sender', 'receiver'])
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        // Get all beneficiary names and phone numbers for the current user to check duplicates
        $beneficiaryNames = Beneficiary::where('user_id', Auth::id())
            ->pluck('full_name')
            ->toArray();
        
        $beneficiaryPhones = Beneficiary::where('user_id', Auth::id())
            ->whereNotNull('phone_number')
            ->pluck('phone_number')
            ->toArray();

        return view('user.history', compact('transactions', 'beneficiaryNames', 'beneficiaryPhones'));
    }
}
