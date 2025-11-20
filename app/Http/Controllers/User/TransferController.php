<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Services\CurrencyService;
use App\Models\PaymentMethod;
use App\Models\FakeCard;
use App\Models\FakeBankAccount;
use App\Services\NotificationService;
use App\Models\TransferService;


class TransferController extends Controller
{
  public function index(Request $request)
{
    $users = User::where('id', '!=', Auth::id())->get();
    $beneficiaries = Beneficiary::where('user_id', Auth::id())->get();
    $currencies = CurrencyService::getSupportedCurrencies();
    $selectedCurrency = session('user_currency', 'USD');

    $availableAgents = User::role('Agent')
        ->where('is_available', true)
        ->where('status', 'active')
        ->get()
        ->filter(fn($agent) => $agent->isCurrentlyAvailable())
        ->values();

    $fakeCards = FakeCard::all();
    $fakeBankAccounts = FakeBankAccount::all();

    // Define all available cards and banks first (used if no service is selected)
    $cards = Auth::user()->paymentMethods()->where('type', 'credit_card')->get();
    $banks = Auth::user()->paymentMethods()->where('type', 'bank_account')->get();

    // Get selected transfer service if provided
    $selectedService = null;
    $payoutType = $request->query('payout');

    if ($request->transfer_service_id) {
        $selectedService = TransferService::find($request->transfer_service_id);
        // Optionally, you can override $payoutType with the service's type
        // if you prefer the DB value over the URL parameter
        if ($selectedService) {
            $payoutType = $selectedService->destination_type;
        }
    }

    $defaultPaymentMethod = null;

    if ($request->has('service') || $request->has('transfer_service_id')) {
        // Support both 'service' and 'transfer_service_id' parameters
        $serviceId = $request->get('service') ?? $request->get('transfer_service_id');
        $selectedService = TransferService::find($serviceId);
        
        // START: LOGIC TO DETERMINE AVAILABLE PAYMENT METHODS BASED ON SELECTED SERVICE
        if ($selectedService) {
            switch ($selectedService->source_type) {
                case 'wallet':
                    // Source is the user's wallet balance. Clear card/bank lists.
                    $cards = collect([]);
                    $banks = collect([]);
                    $defaultPaymentMethod = 'wallet';
                    break;
                case 'card':
                case 'credit_card': // Handle both variations
                    // Source must be a card. Only show cards.
                    $banks = collect([]); // Clear banks
                    $defaultPaymentMethod = 'credit_card';
                    break;
                case 'bank':
                case 'bank_account': // Handle both variations
                    // Source must be a bank account. Only show banks.
                    $cards = collect([]); // Clear cards
                    $defaultPaymentMethod = 'bank_account';
                    break;
                default:
                    // Fallback, do nothing (keep all as defined above)
                    break;
            }
        }
        // END: LOGIC TO DETERMINE AVAILABLE PAYMENT METHODS BASED ON SELECTED SERVICE
    } 
    // If no service is selected, the initial $cards and $banks (all) will be used.
    // The rest of the function remains the same.

    return view('user.transfer', [
        'users' => $users,
        'beneficiaries' => $beneficiaries,
        'currencies' => $currencies,
        'selectedCurrency' => $selectedCurrency,
        'availableAgents' => $availableAgents,
        'cards' => $cards, // Sender's cards
        'banks' => $banks, // Sender's banks
        'selectedService' => $selectedService,
        'payoutType' => $payoutType,
        'defaultPaymentMethod' => $defaultPaymentMethod,
        'fakeCards' => $fakeCards, // New: Recipient's fake cards
        'fakeBankAccounts' => $fakeBankAccounts, // New: Recipient's fake banks
    ]);
}



public function send(Request $request)
{
    $currencies = CurrencyService::getSupportedCurrencies();
    $selectedCurrency = session('user_currency', 'USD');

    // --- START: CONSOLIDATED VALIDATION LOGIC ---

    // 1. Get transfer service early to determine required fields
    $transferService = null;
    $destinationType = null;
    if ($request->filled('transfer_service_id')) {
        $transferService = TransferService::find($request->transfer_service_id);
        if ($transferService) {
            $destinationType = $transferService->destination_type;
        }
    }

    // 2. Define base validation rules
    $rules = [
        'search_type' => 'required|in:email,phone',
        'amount' => 'required|numeric|min:0.01',
        'currency' => 'nullable|string|in:' . implode(',', array_keys($currencies)),
        'email' => [
            'nullable',
            'email',
            function ($attribute, $value, $fail) use ($request, $destinationType) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                $isCashPickup = $request->service_type === 'cash_pickup';

                if ($isNormalTransfer && !$isCashPickup && $request->search_type === 'email' && empty($value)) {
                    $fail("The {$attribute} field is required when search type is email.");
                }
            },
            function ($attribute, $value, $fail) use ($destinationType, $request) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                $isCashPickup = $request->service_type === 'cash_pickup';
                
                if ($isNormalTransfer && !$isCashPickup && !empty($value) && !User::where($attribute, $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }
        ],
        'phone' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) use ($request, $destinationType) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                $isCashPickup = $request->service_type === 'cash_pickup';

                if ($isNormalTransfer && !$isCashPickup && $request->search_type === 'phone' && empty($value)) {
                    $fail("The {$attribute} field is required when search type is phone.");
                }
            },
            function ($attribute, $value, $fail) use ($destinationType, $request) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                $isCashPickup = $request->service_type === 'cash_pickup';
                
                if ($isNormalTransfer && !$isCashPickup && !empty($value) && !User::where($attribute, $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }
        ],
        'service_type' => 'nullable|in:wallet_to_wallet,transfer_via_agent,cash_pickup',
        'agent_id' => [
            'nullable',
            'exists:users,id',
            function ($attribute, $value, $fail) use ($request) {
                if (in_array($request->service_type, ['cash_pickup', 'transfer_via_agent']) && empty($value)) {
                    $fail('An agent must be selected for this service type.');
                }
            }
        ],
        'payment_method' => 'required|in:wallet,credit_card,bank_account',
        'card_id' => 'nullable|exists:payment_methods,id',
        'bank_id' => 'nullable|exists:payment_methods,id',
        'transfer_service_id' => 'nullable|exists:transfer_services,id',
        'recipient_card_id' => 'nullable|exists:fake_cards,id',
        'recipient_bank_id' => 'nullable|exists:fake_bank_accounts,id',
        'recipient_card_nickname' => 'nullable|string|max:255',
        'recipient_bank_nickname' => 'nullable|string|max:255',
        'recipient_name' => 'nullable|string|max:255',
        // Add fields for non-user recipients for storage in the transaction record if needed
        'cardholder_name' => 'nullable|string|max:255',
        'card_number' => 'nullable|string|max:16',
        'account_holder_name' => 'nullable|string|max:255',
        'bank_name' => 'nullable|string|max:255',
        'account_number' => 'nullable|string|max:50',
    ];

    // 3. Conditionally add 'required' rules based on the service's destination type
    if ($destinationType === 'card') {
        $rules = array_merge($rules, [
            'cardholder_name' => 'required|string|max:255',
            'card_number' => 'required|string|digits:16',
            'recipient_name' => 'required|string|max:255',
        ]);
    } elseif ($destinationType === 'bank') {
        $rules = array_merge($rules, [
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
        ]);
    } elseif ($request->service_type === 'cash_pickup') {
        $rules = array_merge($rules, [
            'recipient_name' => 'required|string|max:255',
        ]);
    }

    if ($transferService) {
        $rules['currency'] = 'required|string|in:' . $transferService->destination_currency;
    }

    // 4. Run the single, consolidated validation
    $request->validate($rules);

    // --- END: CONSOLIDATED VALIDATION LOGIC ---

    $sender = Auth::user();
    $sender->refresh();

    $amount = (float) $request->amount;
    
    if ($transferService && in_array($transferService->destination_type, ['card', 'bank'])) {
        $serviceType = 'wallet_to_wallet';
    } else {
        $serviceType = $request->service_type; 
    }

    // Calculate amounts based on whether a transfer service is used
    if ($transferService) {
        $destinationAmount = $amount;
        $fee = (float) $transferService->fee;
        $exchangeRate = (float) $transferService->exchange_rate;
        // The original calculation seems to be: Sender pays $ (destination / rate) + fee, for a destination currency amount.
        // It's unconventional but kept for consistency, assuming $amount is the desired DESTINATION amount in destination currency.
        $amountInUsd = ($destinationAmount / $exchangeRate) + $fee;
        $amountInUsd = round($amountInUsd, 2);
        
        if ($destinationAmount <= 0) {
            return back()->withInput()->withErrors(['amount' => 'Amount must be greater than zero.']);
        }
        
        $transactionCurrency = $transferService->destination_currency;
    } else {
        $transactionCurrency = $request->currency ?? $selectedCurrency;
        
        if ($transactionCurrency === 'USD') {
            $amountInUsd = $amount;
        } else {
            // NOTE: Original code's conversion direction looks reversed (amount * rate, not amount / rate for USD -> X).
            // Retaining the original call `CurrencyService::convert($amount, 'USD', $transactionCurrency)`
            // which in a proper service would convert $amount FROM $transactionCurrency TO USD (or vice versa, depending on implementation).
            // Assuming it converts $amount in $transactionCurrency to $amountInUsd.
            $amountInUsd = round(CurrencyService::convert($amount, 'USD', $transactionCurrency), 2);
            
            if ($amountInUsd > ($amount * 100) && $amount > 100) {
                \Log::warning("Suspicious currency conversion: {$amount} {$transactionCurrency} = {$amountInUsd} USD");
                return back()->withInput()->withErrors([
                    'amount' => "Currency conversion failed. Please try again or contact support."
                ]);
            }
        }
        
        $fee = 0;
        $destinationAmount = $amount;
    }

    // ✅ DETERMINE RECEIVER BEFORE USING IT
    $receiver = null;
    $isCardOrBankPayout = $transferService && in_array($transferService->destination_type, ['card', 'bank']);
    $isCashPickup = $serviceType === 'cash_pickup';

    if (!$isCardOrBankPayout && !$isCashPickup) {
        // Standard wallet-to-wallet transfer
        if ($request->search_type === 'email') {
            $receiver = User::where('email', $request->email)->first();
        } else {
            $receiver = User::where('phone', $request->phone)->first();
        }
        
        if (!$receiver) {
            return back()->withInput()->withErrors(['error' => 'Receiver not found.']);
        }
    } 
    // ELSE: $receiver remains null, which is intended for non-user recipients.
    
    // Check for sending to self ONLY for wallet-to-wallet
    if ($receiver && $receiver->id === $sender->id) {
        return back()->withInput()->withErrors(['error' => 'You cannot send money to yourself.']);
    }

 if ($serviceType === 'cash_pickup' || $serviceType === 'transfer_via_agent') {
    $request->validate(['agent_id' => 'required|exists:users,id']);
    $selectedAgent = User::findOrFail($request->agent_id);
    
    if (!$selectedAgent->hasRole('Agent') || !$selectedAgent->is_available || $selectedAgent->status !== 'active') {
        return back()->withInput()->withErrors(['agent_id' => 'Selected agent is not available.']);
    }

    $commissionRate = $selectedAgent->commission ?? 0;
    $commissionAmount = round(($amountInUsd * $commissionRate) / 100, 2);
    
    // ✅ FIX: DEDUCT MONEY FROM SENDER IMMEDIATELY FOR CASH PICKUP
    if ($serviceType === 'cash_pickup') {
        // Validation and deduction logic (kept as it handles sender's funds)
        if ($request->payment_method === 'wallet') {
            $senderBalance = (float) ($sender->balance ?? 0);
            if ($senderBalance < $amountInUsd) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient wallet balance. You need " . number_format($amountInUsd, 2) . " USD"
                ]);
            }
            
            // Deduct from sender's wallet immediately
            $sender->balance -= $amountInUsd;
            $sender->save();
            
        } elseif ($request->payment_method === 'credit_card') {
            $request->validate(['card_id' => 'required|exists:payment_methods,id']);
            $paymentMethod = PaymentMethod::findOrFail($request->card_id);
            $card = FakeCard::where('card_number', 'like', '%'.$paymentMethod->last4)->first();
            
            if (!$card || $card->balance < $amountInUsd) {
                $available = $card ? number_format($card->balance, 2) : '0.00';
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient card balance. Available: $available USD, Required: " . number_format($amountInUsd, 2) . " USD"
                ]);
            }
            
            $card->balance -= $amountInUsd;
            $card->save();
            
        } elseif ($request->payment_method === 'bank_account') {
            $request->validate(['bank_id' => 'required|exists:payment_methods,id']);
            $paymentMethod = PaymentMethod::findOrFail($request->bank_id);
            $bank = FakeBankAccount::where('account_number', 'like', '%'.$paymentMethod->last4)->first();
            
            if (!$bank || $bank->balance < $amountInUsd) {
                $available = $bank ? number_format($bank->balance, 2) : '0.00';
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient bank balance. Available: $available USD, Required: " . number_format($amountInUsd, 2) . " USD"
                ]);
            }
            
            $bank->balance -= $amountInUsd;
            $bank->save();
        }
    }
    // For transfer_via_agent, money stays with sender until agent completes
    
    $status = 'in_progress';
    
    $transaction = Transaction::create([
        'sender_id' => $sender->id,
        // Set receiver_id to null for non-user transactions (cash pickup/agent transfer)
        'receiver_id' => $receiver?->id, 
        'amount' => $destinationAmount,
        'amount_usd' => $amountInUsd,
        'currency' => $transactionCurrency,
        'status' => $status,
        'agent_id' => $request->agent_id,
        'service_type' => $serviceType,
        'payment_method' => $request->payment_method,
        'transfer_service_id' => $transferService?->id,
        'fee_percent' => $commissionRate,
        'fee_amount_usd' => $commissionAmount,
        // Store recipient details on the transaction for non-user transfers
        'recipient_name' => $request->recipient_name, 
        'recipient_phone' => $request->phone,
        // Add other recipient fields if necessary for non-user transfers
    ]);

    NotificationService::sendAgentNotification(
        $selectedAgent,
        $serviceType === 'cash_pickup' ? 'New Cash Pickup Request' : 'New Transfer Request',
        "You have a new " . ($serviceType === 'cash_pickup' ? 'cash pickup' : 'transfer') . " of " . CurrencyService::format($transaction->amount, $transaction->currency ?? 'USD') . " from {$sender->name} for " . ($request->recipient_name ?? 'a recipient') . ".",
        $transaction
    );

    $message = $serviceType === 'cash_pickup' 
        ? 'Your cash pickup request has been sent to the agent. The recipient can collect the cash once the agent accepts and completes the transaction.'
        : 'Your transfer request has been sent to the selected agent.';

    NotificationService::transferInitiated($transaction);

    return redirect()->route('user.transactions')->with('success', $message);
}

    // Payment method validation and balance checks
    $paymentMethod = null;
    $card = null;
    $bank = null;
    // Removed $recipientCard/$recipientBank declaration here as they are no longer needed
    // for creating/updating a fake user account.

    // If a transfer service is used for card/bank payouts, find/create the *FakeCard/FakeBankAccount*
    // but DO NOT create a User record. The Transaction's receiver_id will be null.
    if ($transferService && in_array($transferService->destination_type, ['card', 'bank'])) {
        if ($transferService->destination_type === 'card') {
             // Find or create the FakeCard (this is fine, as it's not the users table)
            $recipientCard = FakeCard::firstOrCreate(
                ['card_number' => $request->card_number],
                [
                    // Since we're not creating a user, link it to the sender's ID or a system user ID if required by the model
                    'user_id' => $sender->id, // A placeholder/system ID can be used here. For simplicity, using sender's ID
                    'nickname' => $request->recipient_card_nickname ?? 'New Card',
                    'cardholder_name' => $request->cardholder_name,
                    'balance' => 0.00,
                ]
            );
        } elseif ($transferService->destination_type === 'bank') {
             // Find or create the FakeBankAccount (this is fine, as it's not the users table)
            $recipientBank = FakeBankAccount::firstOrCreate(
                ['account_number' => $request->account_number],
                [
                    'user_id' => $sender->id, // A placeholder/system ID can be used here. For simplicity, using sender's ID
                    'nickname' => $request->recipient_bank_nickname ?? 'New Bank Account',
                    'account_holder_name' => $request->account_holder_name,
                    'bank_name' => $request->bank_name,
                    'balance' => 0.00,
                ]
            );
        }
        // $receiver remains NULL for these transfers
    }
    // ELSE: $receiver must be set for a wallet-to-wallet transfer at this point.

    // Payment method balance checks (Sender's side) - same as before
    if ($request->payment_method === 'credit_card') {
        $request->validate(['card_id' => 'required|exists:payment_methods,id']);
        $paymentMethod = PaymentMethod::findOrFail($request->card_id);
        $card = FakeCard::where('card_number', 'like', '%'.$paymentMethod->last4)->first();
        
        if (!$card || $card->balance < $amountInUsd) {
            $available = $card ? number_format($card->balance, 2) : '0.00';
            return back()->withInput()->withErrors([
                'amount' => "Insufficient balance on selected credit card. Available: $available USD, Required: " . number_format($amountInUsd, 2) . " USD"
            ]);
        }
    } elseif ($request->payment_method === 'bank_account') {
        $request->validate(['bank_id' => 'required|exists:payment_methods,id']);
        $paymentMethod = PaymentMethod::findOrFail($request->bank_id);
        $bank = FakeBankAccount::where('account_number', 'like', '%'.$paymentMethod->last4)->first();
        
        if (!$bank || $bank->balance < $amountInUsd) {
            $available = $bank ? number_format($bank->balance, 2) : '0.00';
            return back()->withInput()->withErrors([
                'amount' => "Insufficient balance in selected bank account. Available: $available USD, Required: " . number_format($amountInUsd, 2) . " USD"
            ]);
        }
    } else {
        $senderBalance = (float) ($sender->balance ?? 0);
        
        if ($senderBalance < $amountInUsd) {
            $amountInSelectedCurrency = CurrencyService::format($destinationAmount, $transactionCurrency);
            $balanceInSelectedCurrency = CurrencyService::format(
                CurrencyService::convert($senderBalance, $transactionCurrency, 'USD'), 
                $transactionCurrency
            );
            
            return back()->withInput()->withErrors([
                'amount' => "Insufficient wallet balance. You need " . number_format($amountInUsd, 2) . " USD (≈ {$amountInSelectedCurrency}), but you only have {$senderBalance} USD (≈ {$balanceInSelectedCurrency}) available."
            ]);
        }
    }

    // Process transaction based on service type
    if ($serviceType === 'wallet_to_wallet') {
        $admin = User::role('Admin')->first();
        $adminCommission = $admin->commission ?? 0;
        $feeInUsd = $transferService ? (float) $transferService->fee : round(($amountInUsd * $adminCommission) / 100, 2);
        $transactionStatus = $amountInUsd > 1000 ? 'suspicious' : 'completed';

        // NOTE: The balance deduction/addition logic below only runs if $transactionStatus is NOT 'suspicious'.
        if ($transactionStatus !== 'suspicious') {
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

            $receiverAmount = $amountInUsd - $feeInUsd;

            // Add the money to the destination (Wallet, FakeCard, or FakeBankAccount)
            if ($transferService && $transferService->destination_type === 'card' && isset($recipientCard)) {
                $recipientCard->balance += $receiverAmount;
                $recipientCard->save();
            } elseif ($transferService && $transferService->destination_type === 'bank' && isset($recipientBank)) {
                $recipientBank->balance += $receiverAmount;
                $recipientBank->save();
            } elseif ($receiver) { // Only for true wallet-to-wallet transfers
                $receiver->balance += $receiverAmount;
                $receiver->save();
            }
            
            // NOTE: $admin is assumed to be a valid User and exists.
            if ($admin) {
                $admin->balance += $feeInUsd;
                $admin->save();
            }
        }

        $transaction = Transaction::create([
            'sender_id' => $sender->id,
            // $receiver is NULL for card/bank payouts, ID for wallet-to-wallet
            'receiver_id' => $receiver?->id, 
            'amount' => $amount,
            'amount_usd' => $amountInUsd,
            'currency' => $transactionCurrency,
            'status' => $transactionStatus,
            'agent_id' => $admin?->id,
            'service_type' => $serviceType,
            'payment_method' => $request->payment_method,
            'fee_percent' => $adminCommission,
            'fee_amount_usd' => $feeInUsd,
            'transfer_service_id' => $transferService?->id,
            // Store recipient details for non-user transfers
            'recipient_name' => $request->recipient_name, 
            'recipient_card_number' => $request->card_number,
            'recipient_account_number' => $request->account_number,
            // Add other recipient fields if necessary
        ]);

        $message = $transactionStatus === 'suspicious'
            ? 'Transaction flagged as suspicious and awaiting admin approval.'
            : 'Money sent successfully!';

        NotificationService::transferInitiated($transaction);

        if ($transactionStatus === 'suspicious') {
            NotificationService::transferPendingReview($transaction);
        } else {
            NotificationService::transferCompleted($transaction);
        }

        return redirect()->route('user.transactions')->with('success', $message);
        
    } else {
        // Transfer via agent (fallback for transfer_via_agent/cash_pickup if not handled above, but agent transfers are handled above)
        // This 'else' block seems redundant if $serviceType is correctly set to 'wallet_to_wallet' for card/bank payouts.
        // Keeping it for safety, but modifying to set $receiver_id to null if it's not a user-to-user transfer.
        
        $status = $request->agent_id ? 'in_progress' : 'pending_agent';
        
        $transaction = Transaction::create([
            'sender_id' => $sender->id,
            // Set receiver_id to null for non-user transactions
            'receiver_id' => $receiver?->id, 
            'amount' => $destinationAmount,
            'currency' => $transactionCurrency,
            'status' => $status,
            'agent_id' => $request->agent_id ?? null,
            'service_type' => $serviceType,
            'payment_method' => $request->payment_method,
            'transfer_service_id' => $transferService?->id,
            'fee_percent' => 0,
            'fee_amount_usd' => 0,
            // Store recipient details for non-user transfers
            'recipient_name' => $request->recipient_name, 
            'recipient_phone' => $request->phone,
        ]);

        if ($transaction->agent_id) {
            $transaction->loadMissing('agent');
            NotificationService::sendAgentNotification(
                $transaction->agent,
                'New money transfer request',
                "You have a new transfer of " . CurrencyService::format($transaction->amount, $transaction->currency ?? 'USD') . " from {$sender->name} to " . ($request->recipient_name ?? 'a recipient') . ".",
                $transaction
            );
        }

        $message = $request->agent_id
            ? 'Your transfer request has been sent to the selected agent.'
            : 'Your transfer request has been sent. An agent will be assigned soon.';

        NotificationService::transferInitiated($transaction);

        return redirect()->route('user.transactions')->with('success', $message);
    }
}
}