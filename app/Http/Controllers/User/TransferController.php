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
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $sender = Auth::user();
        $receiver = User::find($request->receiver_id);
        $amount = $request->amount;

        if ($sender->balance < $amount) {
            return back()->with('error', 'Insufficient balance.');
        }

        // Start transaction
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
