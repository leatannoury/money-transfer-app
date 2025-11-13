<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\PaymentMethod;
use App\Models\FakeCard;
use App\Models\FakeBankAccount;



class TransferController extends Controller
{

public function index()
{
    $users = User::where('id', '!=', Auth::id())->get();
    $beneficiaries = Beneficiary::where('user_id', Auth::id())->get();
    $availableAgents = User::role('Agent')
        ->where('is_available', true)
        ->where('status', 'active')
        ->get()
        ->filter(fn($agent) => $agent->isCurrentlyAvailable())
        ->values();

    // Fetch user's saved payment methods
    $cards = Auth::user()->paymentMethods()->where('type', 'credit_card')->get();
    $banks = Auth::user()->paymentMethods()->where('type', 'bank_account')->get();

    return view('user.transfer', compact('users', 'beneficiaries', 'availableAgents', 'cards', 'banks'));
}


public function send(Request $request)
{
    // Basic validation
    $request->validate([
        'search_type' => 'required|in:email,phone',
        'amount' => 'required|numeric|min:1',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'service_type' => 'required|in:wallet_to_wallet,transfer_via_agent',
        'agent_id' => 'nullable|exists:users,id',
        'payment_method' => 'required|in:wallet,credit_card,bank_account',
    ]);

    // Require agent_id if needed
    if ($request->service_type === 'transfer_via_agent') {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);
        $selectedAgent = User::findOrFail($request->agent_id);
        if (!$selectedAgent->hasRole('Agent') || !$selectedAgent->is_available || $selectedAgent->status !== 'active') {
            return back()->withInput()->withErrors(['agent_id' => 'Selected agent is not available.']);
        }
    }

    $sender = Auth::user();
    $amount = $request->amount;
    $serviceType = $request->service_type;

    // Handle receiver
    if ($request->search_type === 'email') {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $receiver = User::where('email', $request->email)->first();
    } else {
        $request->validate(['phone' => 'required|exists:users,phone']);
        $receiver = User::where('phone', $request->phone)->first();
    }

    if ($receiver->id === $sender->id) {
        return back()->withInput()->withErrors(['error' => 'You cannot send money to yourself.']);
    }

    // Determine payment method
 if ($request->payment_method === 'credit_card') {
    $request->validate(['card_id' => 'required|exists:payment_methods,id']);
    $paymentMethod = PaymentMethod::findOrFail($request->card_id);

    // fetch balance from fake_cards
   $card = FakeCard::where('card_number', 'like', '%'.$paymentMethod->last4)->firstOrFail();

    // Check card balance
    if ($card->balance < $amount) {
        return back()->withInput()->withErrors([
            'amount' => "Insufficient balance on selected credit card. Available: {$card->balance}"
        ]);
    }
} elseif ($request->payment_method === 'bank_account') {
    $request->validate(['bank_id' => 'required|exists:payment_methods,id']);
    $paymentMethod = PaymentMethod::findOrFail($request->bank_id);

    // fetch balance from fake_bank_accounts
    $bank = FakeBankAccount::where('account_number', 'like', '%'.$paymentMethod->last4)->firstOrFail();

    // Check bank balance
    if ($bank->balance < $amount) {
        return back()->withInput()->withErrors([
            'amount' => "Insufficient balance in selected bank account. Available: {$bank->balance}"
        ]);
    }
} else {
    $paymentMethod = 'wallet';
    if ($sender->balance < $amount) {
        return back()->withInput()->withErrors(['amount' => "Insufficient wallet balance. Available: {$sender->balance}"]);
    }
}


    // --- Process transfer ---
    if ($serviceType === 'wallet_to_wallet') {
        if ($paymentMethod === 'wallet') {
            $sender->balance -= $amount;
            $receiver->balance += $amount;
            $sender->save();
            $receiver->save();
        } elseif ($request->payment_method === 'credit_card') {
    $card->balance -= $amount;
    $card->save();

    $receiver->balance += $amount;
    $receiver->save();
} elseif ($request->payment_method === 'bank_account') {
    $bank->balance -= $amount;
    $bank->save();

    $receiver->balance += $amount;
    $receiver->save();
}
    }

    Transaction::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'amount' => $amount,
        'status' => 'completed',
        'agent_id' => $request->service_type === 'transfer_via_agent' ? $request->agent_id : null,
        'service_type' => $serviceType,
        'payment_method' => $request->payment_method,
    ]);

    return redirect()->route('user.transactions')->with('success', 'Money sent successfully!');
}

}