<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Services\CurrencyService;
use App\Models\AgentNotification;
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

        // Get available agents
        $availableAgents = User::role('Agent')
            ->where('is_available', true)
            ->where('status', 'active')
            ->get()
            ->filter(fn($agent) => $agent->isCurrentlyAvailable())
            ->values();

        $cards = Auth::user()->paymentMethods()->where('type', 'credit_card')->get();
        $banks = Auth::user()->paymentMethods()->where('type', 'bank_account')->get();

        return view('user.transfer', compact(
            'users',
            'beneficiaries',
            'availableAgents',
            'cards',
            'banks',
            'currencies',
            'selectedCurrency'
        ));
    }

    public function send(Request $request)
    {
        $currencies = CurrencyService::getSupportedCurrencies();
        $selectedCurrency = session('user_currency', 'USD');

        // Basic validation
        $request->validate([
            'search_type' => 'required|in:email,phone',
            'amount' => 'required|numeric|min:1',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'service_type' => 'required|in:wallet_to_wallet,transfer_via_agent',
            'agent_id' => 'nullable|exists:users,id',
            'payment_method' => 'required|in:wallet,credit_card,bank_account',
            'card_id' => 'nullable|exists:payment_methods,id',
            'bank_id' => 'nullable|exists:payment_methods,id',
        ]);

        $sender = Auth::user();
        $amount = $request->amount;
        $serviceType = $request->service_type;
        $transactionCurrency = $request->currency ?? $selectedCurrency;
        $amountInUsd = round(CurrencyService::convert($amount, 'USD', $transactionCurrency), 2);

        // Agent validation
        if ($serviceType === 'transfer_via_agent') {
            $request->validate(['agent_id' => 'required|exists:users,id']);
            $selectedAgent = User::findOrFail($request->agent_id);
            if (!$selectedAgent->hasRole('Agent') || !$selectedAgent->is_available || $selectedAgent->status !== 'active') {
                return back()->withInput()->withErrors(['agent_id' => 'Selected agent is not available.']);
            }
        }

        // Determine receiver
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

        // Payment method and balance checks
        if ($request->payment_method === 'credit_card') {
            $request->validate(['card_id' => 'required|exists:payment_methods,id']);
            $paymentMethod = PaymentMethod::findOrFail($request->card_id);
            $card = FakeCard::where('card_number', 'like', '%'.$paymentMethod->last4)->firstOrFail();
            if ($card->balance < $amount) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient balance on selected credit card. Available: {$card->balance}"
                ]);
            }
        } elseif ($request->payment_method === 'bank_account') {
            $request->validate(['bank_id' => 'required|exists:payment_methods,id']);
            $paymentMethod = PaymentMethod::findOrFail($request->bank_id);
            $bank = FakeBankAccount::where('account_number', 'like', '%'.$paymentMethod->last4)->firstOrFail();
            if ($bank->balance < $amount) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient balance in selected bank account. Available: {$bank->balance}"
                ]);
            }
        } else {
            // Wallet
            if ($sender->balance < $amount) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient wallet balance. Available: {$sender->balance}"
                ]);
            }
        }

        // Process wallet or payment methods
       if ($serviceType === 'wallet_to_wallet') {
    $admin = User::role('Admin')->first();
    $adminCommission = $admin->commission ?? 0;
    $fee = round(($amount * $adminCommission) / 100, 2);

    // Determine if transaction is suspicious
    $transactionStatus = $amount > 1000 ? 'suspicious' : 'completed';

    if ($transactionStatus !== 'suspicious') {
        // Only update balances if NOT suspicious
        if ($request->payment_method === 'wallet') {
            $sender->balance -= $amount;
        } elseif ($request->payment_method === 'credit_card') {
            $card->balance -= $amount;
            $card->save();
        } elseif ($request->payment_method === 'bank_account') {
            $bank->balance -= $amount;
            $bank->save();
        }

        $sender->save();
        $receiver->balance += $amount - $fee;
        $receiver->save();
        $admin->balance += $fee;
        $admin->save();
    }

    // Create transaction (balances updated only if not suspicious)
    Transaction::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'amount' => $amount,
        'currency' => $transactionCurrency,
        'status' => $transactionStatus,
        'agent_id' => $admin->id,
        'service_type' => $serviceType,
        'payment_method' => $request->payment_method,
        // 'fee' => $fee, // store fee for later admin approval
    ]);

    $msg = $transactionStatus === 'suspicious'
        ? 'Transaction flagged as suspicious and awaiting admin approval.'
        : 'Money sent successfully!';


    return redirect()->route('user.transactions')->with('success', $msg);
}

        // Transfer via agent
        $status = $request->agent_id ? 'in_progress' : 'pending_agent';
        $transaction = Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'currency' => $transactionCurrency,
            'status' => $status,
            'agent_id' => $request->agent_id ?? null,
            'service_type' => $serviceType,
            'payment_method' => $request->payment_method,
        ]);

        if ($transaction->agent_id) {
            AgentNotification::create([
                'agent_id' => $transaction->agent_id,
                'transaction_id' => $transaction->id,
                'title' => 'New money transfer request',
                'message' => "You have a new transfer of \${$transaction->amount} from {$sender->name} to {$receiver->name}.",
            ]);
        }

        $message = $request->agent_id
            ? 'Your transfer request has been sent to the selected agent.'
            : 'Your transfer request has been sent. An agent will be assigned soon.';

        return redirect()->route('user.transactions')->with('success', $message);
    }
}
