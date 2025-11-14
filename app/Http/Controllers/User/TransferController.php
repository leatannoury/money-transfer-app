<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Services\CurrencyService;
use Illuminate\Validation\Rule;
use App\Models\AgentNotification; // ✅ added for agent notifications
use App\Models\PaymentMethod;
use App\Models\FakeCard;
use App\Models\FakeBankAccount;

class TransferController extends Controller
{
    public function index()
    {
        // Show form to send money
        $users = User::where('id', '!=', Auth::id())->get();
        $beneficiaries = Beneficiary::where('user_id', Auth::id())->get();
         $currencies = CurrencyService::getSupportedCurrencies();
        $selectedCurrency = session('user_currency', 'USD');
        
        // Get available agents (users with 'Agent' role who are currently available)
        $availableAgents = User::role('Agent')
            ->where('is_available', true)
            ->where('status', 'active')
            ->get()
            ->filter(function($agent) {
                return $agent->isCurrentlyAvailable();
            })
            ->values();
             $cards = Auth::user()->paymentMethods()->where('type', 'credit_card')->get();
    $banks = Auth::user()->paymentMethods()->where('type', 'bank_account')->get();

        
        return view('user.transfer', compact('users', 'beneficiaries', 'availableAgents'));
    }
public function send(Request $request)
{
    // Basic validation
        $currencies = CurrencyService::getSupportedCurrencies();

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
    $transactionCurrency = $request->currency;
    $amountInUsd = round(CurrencyService::convert($amount, 'USD', $transactionCurrency), 2);

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
    } if ($request->payment_method === 'credit_card') {
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
        return back()->withInput()->withErrors(['error' => 'Please enter a valid amount greater than 0.']);
    }
    
    // Check balance for all transaction types
    if ($sender->balance < $amount) {
        $balanceFormatted = number_format($sender->balance, 2);
        return back()->withInput()->withErrors(['amount' => "You don't have enough balance to complete this transfer. Your current balance is \${$balanceFormatted}."]);
    }
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

        // Direct wallet-to-wallet
        $sender->balance -= $amount;
        $receiver->balance += $amount;
        $sender->save();
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
                        'currency' => $transactionCurrency,

            'status' => 'completed', // ✅ completed for wallet transfers
            'agent_id' => null, // Wallet to wallet doesn't need an agent
            'service_type' => $serviceType,
                    'payment_method' => $request->payment_method,

        ]);

        return redirect()->route('user.transactions')->with('success', 'Money sent successfully!');
    } else {
        // Transfer via agent - if agent is pre-selected, set status to in_progress
        // Otherwise, leave it as pending_agent for any agent to accept
        $status = $request->agent_id ? 'in_progress' : 'pending_agent';
        
       $transaction= Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
                        'currency' => $transactionCurrency,

            'status' => $status,
            'agent_id' => $request->agent_id ?? null,
            'service_type' => $serviceType,
        ]);
                    if ($transaction->agent_id) {
                AgentNotification::create([
                    'agent_id'       => $transaction->agent_id,
                    'transaction_id' => $transaction->id,
                    'title'          => 'New money transfer request',
                    'message'        => "You have a new transfer of \${$transaction->amount} from {$sender->name} to {$receiver->name}.",
                ]);
            }

            $message = $request->agent_id 
                ? 'Your transfer request has been sent to the selected agent.' 
                : 'Your transfer request has been sent. An agent will be assigned soon.';
            
            return redirect()->route('user.transactions')->with('success', $message);
        }
    }

}
