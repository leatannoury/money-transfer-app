<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ManageTransactionController extends Controller
{
    public function manageTransaction()
    {
        $transactions = Transaction::with(['sender', 'receiver'])->latest()->get();

        return view('admin.ManageTransaction.manageTransaction', compact('transactions'));
    }
}
