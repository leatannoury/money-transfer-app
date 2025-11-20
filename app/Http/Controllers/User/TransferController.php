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
use App\Models\Provider;
use Illuminate\Validation\Rule;


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

        // Define all available cards and banks first
        $cards = Auth::user()->paymentMethods()->where('type', 'credit_card')->get();
        $banks = Auth::user()->paymentMethods()->where('type', 'bank_account')->get();

        // Get selected transfer service if provided
        $selectedService = null;
        $payoutType = $request->query('payout');
        
        // 🔧 NEW: Initialize providers collection
        $providers = collect([]);

        if ($request->transfer_service_id) {
            $selectedService = TransferService::find($request->transfer_service_id);
            
            if ($selectedService) {
                $payoutType = $selectedService->destination_type;
                
                // 🔧 NEW: Get country code mapping
                $countryCodeMap = [
                    'Turkey' => 'TR',
                    'Jordan' => 'JO',
                    'Egypt' => 'EG',
                    'United Arab Emirates' => 'AE',
                    'UAE' => 'AE',
                    'United States' => 'US',
                    'USA' => 'US',
                ];
                
                // 🔧 NEW: Get the country code for the selected service
                $destinationCountry = $selectedService->destination_country;
                $countryCode = $countryCodeMap[$destinationCountry] ?? null;
                
                // 🔧 NEW: Filter providers by country code
                if ($countryCode) {
                    $providers = Provider::where('is_active', true)
                        ->where('country_code', $countryCode)
                        ->get();
                }
            }
        }

        $defaultPaymentMethod = null;

        if ($request->has('service') || $request->has('transfer_service_id')) {
            $serviceId = $request->get('service') ?? $request->get('transfer_service_id');
            $selectedService = TransferService::find($serviceId);
            
            if ($selectedService) {
                switch ($selectedService->source_type) {
                    case 'wallet':
                        $cards = collect([]);
                        $banks = collect([]);
                        $defaultPaymentMethod = 'wallet';
                        break;
                    case 'card':
                    case 'credit_card':
                        $banks = collect([]);
                        $defaultPaymentMethod = 'credit_card';
                        break;
                    case 'bank':
                    case 'bank_account':
                        $cards = collect([]);
                        $defaultPaymentMethod = 'bank_account';
                        break;
                }
            }
        }

        return view('user.transfer', [
            'users' => $users,
            'beneficiaries' => $beneficiaries,
            'currencies' => $currencies,
            'selectedCurrency' => $selectedCurrency,
            'availableAgents' => $availableAgents,
            'cards' => $cards,
            'banks' => $banks,
            'selectedService' => $selectedService,
            'payoutType' => $payoutType,
            'defaultPaymentMethod' => $defaultPaymentMethod,
            'fakeCards' => $fakeCards,
            'fakeBankAccounts' => $fakeBankAccounts,
            'providers' => $providers, // 🔧 NEW: Pass filtered providers
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

    $serviceType = $request->service_type;
    $isNonLebanonCashPickup = ($serviceType === 'cash_pickup' && $request->has('transfer_service_id'));
    
    // Check if this is a cash pickup (local or international)
    $isCashPickup = ($serviceType === 'cash_pickup');
    
    // 2. Define base validation rules
    $rules = [
        'amount' => 'required|numeric|min:0.01',
        'currency' => 'nullable|string|in:' . implode(',', array_keys($currencies)),
        'payment_method' => 'required|in:wallet,credit_card,bank_account',
        'card_id' => 'nullable|exists:payment_methods,id',
        'bank_id' => 'nullable|exists:payment_methods,id',
        'transfer_service_id' => 'nullable|exists:transfer_services,id',
        'service_type' => [
            'required', 
            'string', 
            'in:user_transfer,agent_transfer,cash_pickup,bank_transfer,card_transfer,wallet_to_wallet'
        ],
    ];

    // Add conditional validation based on service type
    if ($isCashPickup) {
        // For cash pickup, we only need recipient name and phone (no user validation)
        $rules['recipient_name'] = 'required|string|max:255';
        $rules['phone'] = 'required|string|max:50';
        
        // Provider is required for international cash pickup
        if ($isNonLebanonCashPickup) {
            $rules['provider_id'] = 'required|exists:providers,id';
        }
        
        // Agent is required for local cash pickup
        if (!$isNonLebanonCashPickup) {
            $rules['agent_id'] = 'required|exists:users,id';
        }
    } else {
        // For non-cash-pickup services, validate search_type and user existence
        $rules['search_type'] = 'required|in:email,phone';
        $rules['email'] = [
            'nullable',
            'email',
            function ($attribute, $value, $fail) use ($request, $destinationType) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                if ($isNormalTransfer && $request->search_type === 'email' && empty($value)) {
                    $fail("The {$attribute} field is required when search type is email.");
                }
            },
            function ($attribute, $value, $fail) use ($destinationType, $request) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                if ($isNormalTransfer && !empty($value) && !User::where($attribute, $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }
        ];
        $rules['phone'] = [
            'nullable',
            'string',
            function ($attribute, $value, $fail) use ($request, $destinationType) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                if ($isNormalTransfer && $request->search_type === 'phone' && empty($value)) {
                    $fail("The {$attribute} field is required when search type is phone.");
                }
            },
            function ($attribute, $value, $fail) use ($destinationType, $request) {
                $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
                if ($isNormalTransfer && !empty($value) && !User::where($attribute, $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }
        ];
        $rules['agent_id'] = [
            'nullable',
            'exists:users,id',
            function ($attribute, $value, $fail) use ($request) {
                if (in_array($request->service_type, ['transfer_via_agent']) && empty($value)) {
                    $fail('An agent must be selected for this service type.');
                }
            }
        ];
    }

    // 3. Conditionally add 'required' rules based on the service's destination type
    if ($destinationType === 'card') {
        $rules = array_merge($rules, [
            'cardholder_name' => 'required|string|max:255',
            'card_number' => 'required|string|digits:16',
          
        ]);
    } elseif ($destinationType === 'bank') {
        $rules = array_merge($rules, [
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
           
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
    
    // if ($transferService && in_array($transferService->destination_type, ['card', 'bank'])) {
    //     $serviceType = 'wallet_to_wallet';
    // } else {
    //     $serviceType = $request->service_type; 
    // }

    $serviceType = $request->service_type;

    // Calculate amounts based on whether a transfer service is used
    if ($transferService) {
        $destinationAmount = $amount;
        $fee = (float) $transferService->fee;
        $exchangeRate = (float) $transferService->exchange_rate;
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
    $isCashPickup = ($serviceType === 'cash_pickup');    // Only look for receiver user if it's NOT cash pickup and NOT card/bank payout


 if (!$isCardOrBankPayout && !$isCashPickup) { // <-- 🛠️ FIX APPLIED HERE
        // Standard wallet-to-wallet transfer
        if ($request->search_type === 'email') {
            $receiver = User::where('email', $request->email)->first();
        } else {
            $receiver = User::where('phone', $request->phone)->first();
        }
        
        if (!$receiver) {
            // This error will now only trigger for wallet-to-wallet transfers
            return back()->withInput()->withErrors(['error' => 'Receiver not found.']); 
        }
        
        // Check for sending to self ONLY for wallet-to-wallet
        if ($receiver->id === $sender->id) {
            return back()->withInput()->withErrors(['error' => 'You cannot send money to yourself.']);
        }
    }

    // Handle Cash Pickup (both local and international)
    if ($isCashPickup) {
        // For local cash pickup (Lebanon), agent is required and set via $request->agent_id
        // For international cash pickup, agent might not be set initially
        $selectedAgent = null;
        $commissionRate = 0;
        $commissionAmount = 0;
        
        if ($request->agent_id) {
            $selectedAgent = User::findOrFail($request->agent_id);
            
            if (!$selectedAgent->hasRole('Agent') || !$selectedAgent->is_available || $selectedAgent->status !== 'active') {
                return back()->withInput()->withErrors(['agent_id' => 'Selected agent is not available.']);
            }
            
            $commissionRate = $selectedAgent->commission ?? 0;
            $commissionAmount = round(($amountInUsd * $commissionRate) / 100, 2);
        }
        
        // ✅ DEDUCT MONEY FROM SENDER IMMEDIATELY FOR CASH PICKUP
        if ($request->payment_method === 'wallet') {
            $senderBalance = (float) ($sender->balance ?? 0);
            if ($senderBalance < $amountInUsd) {
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient wallet balance. You need " . number_format($amountInUsd, 2) . " USD"
                ]);
            }
            
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
        
        // Set status based on whether it's international or local
        $status = $isNonLebanonCashPickup ? 'completed' : 'in_progress';
        
        $transaction = Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => null, // No receiver user for cash pickup
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->phone,
            'provider_id' => $request->provider_id ?? null,
            'amount' => $destinationAmount,
            'amount_usd' => $amountInUsd,
            'currency' => $transactionCurrency,
            'status' => $status,
            'agent_id' => $request->agent_id ?? null,
            'service_type' => 'cash_pickup',
            'payment_method' => $request->payment_method,
            'transfer_service_id' => $transferService?->id,
            'fee_percent' => $commissionRate,
            'fee_amount_usd' => $commissionAmount,
        ]);

        // Send notification to agent if local cash pickup
        if ($selectedAgent) {
            NotificationService::sendAgentNotification(
                $selectedAgent,
                'New Cash Pickup Request',
                "You have a new cash pickup of " . CurrencyService::format($transaction->amount, $transaction->currency ?? 'USD') . " from {$sender->name} for {$request->recipient_name}.",
                $transaction
            );
        }

        $message = $isNonLebanonCashPickup 
            ? 'Cash pickup request completed. The recipient can collect the cash from the selected provider with the transaction reference.'
            : 'Your cash pickup request has been sent to the agent. The recipient can collect the cash once the agent accepts and completes the transaction.';

        NotificationService::transferInitiated($transaction);

        return redirect()->route('user.transactions')->with('success', $message);
    }

    // Handle Transfer via Agent (non-cash-pickup)
    if ($serviceType === 'transfer_via_agent') {
        $request->validate(['agent_id' => 'required|exists:users,id']);
        $selectedAgent = User::findOrFail($request->agent_id);
        
        if (!$selectedAgent->hasRole('Agent') || !$selectedAgent->is_available || $selectedAgent->status !== 'active') {
            return back()->withInput()->withErrors(['agent_id' => 'Selected agent is not available.']);
        }

        $commissionRate = $selectedAgent->commission ?? 0;
        $commissionAmount = round(($amountInUsd * $commissionRate) / 100, 2);
        
        $status = 'in_progress';
        
        $transaction = Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver?->id,
            'recipient_name' => $request->recipient_name 
    ?? $request->cardholder_name 
    ?? $request->account_holder_name 
    ?? null,

            'recipient_phone' => $request->phone ?? null,
            'provider_id' => null,
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
        ]);

        NotificationService::sendAgentNotification(
            $selectedAgent,
            'New Transfer Request',
            "You have a new transfer of " . CurrencyService::format($transaction->amount, $transaction->currency ?? 'USD') . " from {$sender->name}.",
            $transaction
        );

        NotificationService::transferInitiated($transaction);

        return redirect()->route('user.transactions')->with('success', 'Your transfer request has been sent to the selected agent.');
    }

    // Payment method validation and balance checks for wallet-to-wallet
    $paymentMethod = null;
    $card = null;
    $bank = null;

    // If a transfer service is used for card/bank payouts, find/create the FakeCard/FakeBankAccount
    if ($transferService && in_array($transferService->destination_type, ['card', 'bank'])) {
        if ($transferService->destination_type === 'card') {
            $recipientCard = FakeCard::firstOrCreate(
                ['card_number' => $request->card_number],
                [
                    'user_id' => $sender->id,
                    'nickname' => $request->recipient_card_nickname ?? 'New Card',
                    'cardholder_name' => $request->cardholder_name,
                    'balance' => 0.00,
                ]
            );
        } elseif ($transferService->destination_type === 'bank') {
            $recipientBank = FakeBankAccount::firstOrCreate(
                ['account_number' => $request->account_number],
                [
                    'user_id' => $sender->id,
                    'nickname' => $request->recipient_bank_nickname ?? 'New Bank Account',
                    'account_holder_name' => $request->account_holder_name,
                    'bank_name' => $request->bank_name,
                    'balance' => 0.00,
                ]
            );
        }
    }

    // Payment method balance checks (Sender's side)
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

    // Process wallet-to-wallet transaction
    if ($serviceType === 'wallet_to_wallet') {
        $admin = User::role('Admin')->first();
        $adminCommission = $admin->commission ?? 0;
        $feeInUsd = $transferService ? (float) $transferService->fee : round(($amountInUsd * $adminCommission) / 100, 2);
        $transactionStatus = $amountInUsd > 1000 ? 'suspicious' : 'completed';

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

            // Add the money to the destination
            if ($transferService && $transferService->destination_type === 'card' && isset($recipientCard)) {
                $recipientCard->balance += $receiverAmount;
                $recipientCard->save();
            } elseif ($transferService && $transferService->destination_type === 'bank' && isset($recipientBank)) {
                $recipientBank->balance += $receiverAmount;
                $recipientBank->save();
            } elseif ($receiver) {
                $receiver->balance += $receiverAmount;
                $receiver->save();
            }
            
            if ($admin) {
                $admin->balance += $feeInUsd;
                $admin->save();
            }
        }

        $transaction = Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver?->id,
            'recipient_name' => $request->recipient_name 
    ?? $request->cardholder_name 
    ?? $request->account_holder_name 
    ?? null,

            'recipient_phone' => $request->phone ?? null,
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
            'recipient_name' => $request->recipient_name 
    ?? $request->cardholder_name 
    ?? $request->account_holder_name 
    ?? null,

            'recipient_card_number' => $request->card_number ?? null,
            'recipient_account_number' => $request->account_number ?? null,
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
    }

    // Fallback error
    return back()->withInput()->withErrors(['error' => 'Invalid transaction type.']);
}

}