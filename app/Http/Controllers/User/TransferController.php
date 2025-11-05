<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;

class TransferController extends Controller
{
    public function index()
    {
        // Show form to send money
        $users = User::where('id', '!=', Auth::id())->get();
        return view('user.transfer', compact('users'));
    }

public function send(Request $request)
{
    // Basic validation
    $request->validate([
        'search_type' => 'required|in:email,phone',
        'amount' => 'required|numeric|min:1',
        'email' => 'nullable|email',
        'phone' => 'nullable|string'
    ]);

    $sender = Auth::user();
    $amount = $request->amount;

    // Handle receiver
    if ($request->search_type === 'email') {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'No user found with this email address.'
        ]);
        $receiver = User::where('email', $request->email)->first();
    } else {
        $request->validate(['phone' => 'required|exists:users,phone'], [
            'phone.exists' => 'No user found with this phone number.'
        ]);
        $receiver = User::where('phone', $request->phone)->first();
    }

    // --- Custom logical validation ---
    if ($receiver->id === $sender->id) {
        return back()->withInput()->withErrors(['error' => 'You cannot send money to yourself.']);
    }

    if ($sender->balance < $amount) {
        return back()->withInput()->withErrors(['error' => 'You don’t have enough balance to complete this transfer.']);
    }

    if ($amount <= 0) {
        return back()->withInput()->withErrors(['error' => 'Please enter a valid amount greater than 0.']);
    }

    // --- Process transfer ---
    $sender->balance -= $amount;
    $receiver->balance += $amount;
    $sender->save();
    $receiver->save();

    Transaction::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'amount' => $amount,
        'status' => 'completed',
    ]);

    return redirect()->route('user.transactions')->with('success', 'Money sent successfully!');
}


}
