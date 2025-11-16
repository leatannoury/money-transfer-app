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
            'currency' => 'nullable|string|in:' . implode(',', array_keys($currencies)),
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'service_type' => 'required|in:wallet_to_wallet,transfer_via_agent',
            'agent_id' => 'nullable|exists:users,id',
            'payment_method' => 'required|in:wallet,credit_card,bank_account',
            'card_id' => 'nullable|exists:payment_methods,id',
            'bank_id' => 'nullable|exists:payment_methods,id',
        ]);

        $sender = Auth::user();
        // Refresh sender to get latest balance from database
        $sender->refresh();
        
        $amount = (float) $request->amount;
        $serviceType = $request->service_type;
        $transactionCurrency = $request->currency ?? $selectedCurrency;
        
        // Convert amount from transaction currency to USD
        // If transaction currency is already USD, no conversion needed
        if ($transactionCurrency === 'USD') {
            $amountInUsd = $amount;
        } else {
            $amountInUsd = round(CurrencyService::convert($amount, 'USD', $transactionCurrency), 2);
            
            // Validate conversion result - if it's suspiciously large, the conversion might have failed
            // (e.g., if API fails and defaults to 1.0, 20000 LBP would become 20000 USD)
            // Check if converted amount is more than 100x the original (indicates likely conversion error)
            if ($amountInUsd > ($amount * 100) && $amount > 100) {
                \Log::warning("Suspicious currency conversion: {$amount} {$transactionCurrency} = {$amountInUsd} USD");
                return back()->withInput()->withErrors([
                    'amount' => "Currency conversion failed. Please try again or contact support."
                ]);
            }
        }

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
            // Convert amount to USD for card balance check (assuming card balance is in USD)
            $cardBalanceInUsd = $card->balance;
            if ($cardBalanceInUsd < $amountInUsd) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient balance on selected credit card. Available: {$card->balance} USD"
                ]);
            }
        } elseif ($request->payment_method === 'bank_account') {
            $request->validate(['bank_id' => 'required|exists:payment_methods,id']);
            $paymentMethod = PaymentMethod::findOrFail($request->bank_id);
            $bank = FakeBankAccount::where('account_number', 'like', '%'.$paymentMethod->last4)->firstOrFail();
            // Convert amount to USD for bank balance check (assuming bank balance is in USD)
            $bankBalanceInUsd = $bank->balance;
            if ($bankBalanceInUsd < $amountInUsd) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient balance in selected bank account. Available: {$bank->balance} USD"
                ]);
            }
        } else {
            // Wallet - balance is stored in USD, so compare with amountInUsd
            // Ensure balance is cast to float for proper comparison (default to 0 if null)
            $senderBalance = (float) ($sender->balance ?? 0);
            
            if ($senderBalance < $amountInUsd) {
                $amountInSelectedCurrency = CurrencyService::format($amount, $transactionCurrency);
                $balanceInSelectedCurrency = CurrencyService::format(CurrencyService::convert($senderBalance, $transactionCurrency, 'USD'), $transactionCurrency);
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient wallet balance. You tried to send {$amountInSelectedCurrency} (≈ {$amountInUsd} USD), but you only have {$balanceInSelectedCurrency} (or {$senderBalance} USD) available."
                ]);
            }
        }

        // Process based on service type
        if ($serviceType === 'wallet_to_wallet') {
            // Wallet to wallet transfer
            $admin = User::role('Admin')->first();
            $adminCommission = $admin->commission ?? 0;
            
            // Calculate fee based on USD amount for consistency (all balances are in USD)
            $feeInUsd = round(($amountInUsd * $adminCommission) / 100, 2);

            // Determine if transaction is suspicious (check USD equivalent, not transaction currency)
            $transactionStatus = $amountInUsd > 1000 ? 'suspicious' : 'completed';

            // Only update balances if transaction is not suspicious
            if ($transactionStatus !== 'suspicious') {
                // Deduct from sender's payment method
                if ($request->payment_method === 'wallet') {
                    $sender->balance -= $amountInUsd;
                    $sender->save();
                } elseif ($request->payment_method === 'credit_card') {
                    $card->balance -= $amountInUsd;
                    $card->save();
                } elseif ($request->payment_method === 'bank_account') {
                    $bank->balance -= $amountInUsd;
                    $bank->save();
                }

                // Add to receiver (minus fee)
                $receiver->balance += $amountInUsd - $feeInUsd;
                $receiver->save();
                
                // Add fee to admin
                $admin->balance += $feeInUsd;
                $admin->save();
            }

            // Create transaction record
            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'currency' => $transactionCurrency,
                'status' => $transactionStatus,
                'agent_id' => $admin->id,
                'service_type' => $serviceType,
                'payment_method' => $request->payment_method,
                // 'fee' => $feeInUsd, // store fee for later admin approval
            ]);

            $message = $transactionStatus === 'suspicious'
                ? 'Transaction flagged as suspicious and awaiting admin approval.'
                : 'Money sent successfully!';

            return redirect()->route('user.transactions')->with('success', $message);
        } else {
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
                    'message' => "You have a new transfer of " . CurrencyService::format($transaction->amount, $transactionCurrency) . " from {$sender->name} to {$receiver->name}.",
                ]);
            }

            $message = $request->agent_id
                ? 'Your transfer request has been sent to the selected agent.'
                : 'Your transfer request has been sent. An agent will be assigned soon.';

            return redirect()->route('user.transactions')->with('success', $message);
        }
    }
}

