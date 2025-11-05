<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
public function index()
{
    $user = Auth::user();
    $transactions = \App\Models\Transaction::where('sender_id', $user->id)
        ->orWhere('receiver_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    return view('user.dashboard', compact('user', 'transactions'));
}

}
