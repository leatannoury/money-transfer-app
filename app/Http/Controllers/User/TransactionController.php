<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('sender_id', Auth::id())
                                   ->orWhere('receiver_id', Auth::id())
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return view('user.history', compact('transactions'));
    }
}
