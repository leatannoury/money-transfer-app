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
// In TransferController.php, public function send(Request $request)

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
// The recipient (user account) is only required if it's a Wallet-to-Wallet style transfer.
    // Card/Bank payouts implicitly find the receiver account based on the card/bank details which are later linked to the User.
    // The user receiver is still determined below, but the fields are not required for validation here.
    'email' => [
        'nullable',
        'email',
        // ONLY require email/phone if the destination is NOT a card or bank, and the search type is selected.
        function ($attribute, $value, $fail) use ($request, $destinationType) {
            $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);

            if ($isNormalTransfer && $request->search_type === 'email' && empty($value)) {
                $fail("The {$attribute} field is required when search type is email.");
            }
        },
        // The exists rule should ONLY run for normal transfers, as the Card/Bank
        // transfers might send to a user that doesn't exist yet (created implicitly)
        function ($attribute, $value, $fail) use ($destinationType) {
            $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
            if ($isNormalTransfer && !empty($value) && !User::where($attribute, $value)->exists()) {
                $fail("The selected {$attribute} is invalid.");
            }
        }
    ],
    'phone' => [
        'nullable',
        'string',
        function ($attribute, $value, $fail) use ($request, $destinationType) {
            $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);

            if ($isNormalTransfer && $request->search_type === 'phone' && empty($value)) {
                $fail("The {$attribute} field is required when search type is phone.");
            }
        },
        function ($attribute, $value, $fail) use ($destinationType) {
            $isNormalTransfer = !in_array($destinationType, ['card', 'bank']);
            if ($isNormalTransfer && !empty($value) && !User::where($attribute, $value)->exists()) {
                $fail("The selected {$attribute} is invalid.");
            }
        }
    ],
    // The service_type and agent_id are now also only relevant for wallet-to-wallet style transfers, not for card/bank payouts.
    'service_type' => 'nullable|in:wallet_to_wallet,transfer_via_agent',
    'agent_id' => 'nullable|exists:users,id',
    'payment_method' => 'required|in:wallet,credit_card,bank_account',
    'card_id' => 'nullable|exists:payment_methods,id',
    'bank_id' => 'nullable|exists:payment_methods,id',
    'transfer_service_id' => 'nullable|exists:transfer_services,id',
    
    // Recipient fields are nullable by default
    'recipient_card_id' => 'nullable|exists:fake_cards,id',
    'recipient_bank_id' => 'nullable|exists:fake_bank_accounts,id',
    'recipient_card_nickname' => 'nullable|string|max:255',
    'recipient_bank_nickname' => 'nullable|string|max:255',
];

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
    // If a service is selected, the currency must be present and must match the service's destination currency.
    $rules['currency'] = 'required|string|in:' . $transferService->destination_currency;
}

// 4. Run the single, consolidated validation
$request->validate($rules);

// --- END: CONSOLIDATED VALIDATION LOGIC ---

// Continue with the rest of your logic...
$sender = Auth::user();
$sender->refresh();
// ... (The amount calculation and rest of the function should follow here)

$amount = (float) $request->amount;
// Determine service type: if a transfer service is used for Card/Bank, it's a special type.
// Otherwise, fall back to the form field.
// Map payout services to an existing valid enum value
if ($transferService && in_array($transferService->destination_type, ['card', 'bank'])) {
    $serviceType = 'wallet_to_wallet'; // or another valid ENUM type
} else {
    $serviceType = $request->service_type; 
}


        // Get transfer service if selected
$transferService = null;
if ($request->filled('transfer_service_id')) {
    $transferService = TransferService::find($request->transfer_service_id);
}
        // ... after the main $request->validate([...]); call

// Conditional validation for recipient bank/card details
if ($transferService) {
    if ($transferService->destination_type === 'card') {
        $request->validate([
            'cardholder_name' => 'required|string|max:255',
            'card_number' => 'required|string|digits:16',

        ]);
    } elseif ($transferService->destination_type === 'bank') {
        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',

        ]);
    }
}
// ... continue with the rest of the function (amount calculation, etc.)

        // Calculate amounts based on whether a transfer service is used
        if ($transferService) {
            // When using transfer service:
            // - User enters amount in destination currency
            // - Fee is in USD
            // - We need to calculate the USD equivalent they need to pay
            
            $destinationAmount = $amount; // Amount receiver gets in their currency
            $fee = (float) $transferService->fee; // Fee in USD
            $exchangeRate = (float) $transferService->exchange_rate;
            
            // Calculate USD needed from sender's wallet
            $amountInUsd = ($destinationAmount / $exchangeRate) + $fee;
            $amountInUsd = round($amountInUsd, 2);
            
            // Validate minimum amount after fee
            if ($destinationAmount <= 0) {
                return back()->withInput()->withErrors(['amount' => 'Amount must be greater than zero.']);
            }
            
            $transactionCurrency = $transferService->destination_currency;
        } else {
            // Standard transfer without transfer service
            $transactionCurrency = $request->currency ?? $selectedCurrency;
            
            if ($transactionCurrency === 'USD') {
                $amountInUsd = $amount;
            } else {
                $amountInUsd = round(CurrencyService::convert($amount, 'USD', $transactionCurrency), 2);
                
                // Sanity check for conversion
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

        // Agent validation for transfer_via_agent
        if ($serviceType === 'transfer_via_agent' && !$transferService) {
            $request->validate(['agent_id' => 'required|exists:users,id']);
            $selectedAgent = User::findOrFail($request->agent_id);
            
            if (!$selectedAgent->hasRole('Agent') || !$selectedAgent->is_available || $selectedAgent->status !== 'active') {
                return back()->withInput()->withErrors(['agent_id' => 'Selected agent is not available.']);
            }
        }

// TransferController.php - Replace the block above with this:

// Determine receiver
// Determine receiver
$receiver = null;
$isCardOrBankPayout = $transferService && in_array($transferService->destination_type, ['card', 'bank']);

if (!$isCardOrBankPayout) {
    // --- STANDARD WALLET-TO-WALLET TRANSFER ---
    // Search by email or phone as provided by the user (which is required if it's not a payout)
    if ($request->search_type === 'email') {
        $receiver = User::where('email', $request->email)->first();
    } else {
        $receiver = User::where('phone', $request->phone)->first();
    }
    
    // If receiver is not found for a standard transfer, it's a genuine error.
    if (!$receiver) {
        return back()->withInput()->withErrors(['error' => 'Receiver not found.']);
    }

} else {
    // --- CARD/BANK PAYOUT SERVICE ---
    // The recipient is the person who owns the card/bank account, not necessarily an existing user in the system.
    
    // 1. Try to find the user who already owns this card/bank account (if previously saved)
    if ($destinationType === 'card' && $request->filled('card_number')) {
        $card = FakeCard::where('card_number', $request->card_number)->first();
        if ($card) {
            $receiver = User::find($card->user_id);
        }
    } elseif ($destinationType === 'bank' && $request->filled('account_number')) {
        $bank = FakeBankAccount::where('account_number', $request->account_number)->first();
        if ($bank) {
            $receiver = User::find($bank->user_id);
        }
    }

    // 2. If no existing user is found, create a placeholder 'system' user 
    // to link the new FakeCard/FakeBankAccount to in the next step.
    if (!$receiver) {
        $receiverName = $request->cardholder_name ?? $request->account_holder_name ?? 'Transfer Recipient';
        
        // Generate a unique identifier (hash of card/account number) for the placeholder user's email
        $identifier = $request->card_number ?? $request->account_number ?? 'temp' . time(); 
        $uniqueEmail = 'payout_' . md5($identifier) . '@system.com';

        // Find or create the recipient user
        $receiver = User::firstOrCreate(
            ['email' => $uniqueEmail],
            [
                'name' => $receiverName,
                'password' => \Hash::make(\Str::random(12)), 
                'role' => 'recipient', 
                'status' => 'active', 
                'phone' => null, 
            ]
        );
    }
}

if ($receiver->id === $sender->id) {
    return back()->withInput()->withErrors(['error' => 'You cannot send money to yourself.']);
}

        // Payment method validation and balance checks
        $paymentMethod = null;
        $card = null;
        $bank = null;

        $recipientCard = null;
        $recipientBank = null;



        // If a transfer service is used, process the new card/bank details
        if ($transferService) {
            if ($transferService->destination_type === 'card') {
                // Find existing card or create a new one
                $recipientCard = FakeCard::firstOrCreate(
                    [
                        'card_number' => $request->card_number,
                    ],
                    [
                        'user_id' => $receiver->id, // Assign to the receiver
                        'nickname' => $request->recipient_card_nickname ?? 'New Card',
                        'cardholder_name' => $request->cardholder_name,

                        'balance' => 0.00, // Initialize balance if new
                    ]
                );
            } elseif ($transferService->destination_type === 'bank') {
                // Find existing bank account or create a new one
                $recipientBank = FakeBankAccount::firstOrCreate(
                    [
                        'account_number' => $request->account_number,
                    ],
                    [
                        'user_id' => $receiver->id, // Assign to the receiver
                        'nickname' => $request->recipient_bank_nickname ?? 'New Bank Account',
                        'account_holder_name' => $request->account_holder_name,
                        'bank_name' => $request->bank_name,

                        'balance' => 0.00, // Initialize balance if new
                    ]
                );
            }
        }

        if ($request->payment_method === 'credit_card') {
            $request->validate(['card_id' => 'required|exists:payment_methods,id']);
            $paymentMethod = PaymentMethod::findOrFail($request->card_id);
            // Search the fake card table based on the last 4 digits stored in payment_methods
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
            // Search the fake bank account table based on the last 4 digits stored in payment_methods
            $bank = FakeBankAccount::where('account_number', 'like', '%'.$paymentMethod->last4)->first();
            
            if (!$bank || $bank->balance < $amountInUsd) {
                $available = $bank ? number_format($bank->balance, 2) : '0.00';
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient balance in selected bank account. Available: $available USD, Required: " . number_format($amountInUsd, 2) . " USD"
                ]);
            }
        } else {
            // Wallet payment
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

            // Calculate fee (use transfer service fee or admin commission)
            $feeInUsd = $transferService ? (float) $transferService->fee : round(($amountInUsd * $adminCommission) / 100, 2);

            // Flag suspicious transactions
            $transactionStatus = $amountInUsd > 1000 ? 'suspicious' : 'completed';

            // Only process money movement if not suspicious
            if ($transactionStatus !== 'suspicious') {
                // Deduct from sender
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

                // Credit receiver to correct account type based on transfer service
                if ($transferService && $transferService->destination_type === 'card' && $recipientCard) {
                    $recipientCard->balance += $receiverAmount;
                    $recipientCard->save();
                } elseif ($transferService && $transferService->destination_type === 'bank' && $recipientBank) {
                    $recipientBank->balance += $receiverAmount;
                    $recipientBank->save();
                } else {
                    // Default to wallet (for standard transfers or service to wallet)
                    $receiver->balance += $receiverAmount;
                    $receiver->save();
                }

                // Credit admin with fee
                $admin->balance += $feeInUsd;
                $admin->save();
            }

            // Create transaction record
            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'amount_usd' => $amountInUsd,
                'currency' => $transactionCurrency,
                'status' => $transactionStatus,
                'agent_id' => $admin->id,
                'service_type' => $serviceType,
                'payment_method' => $request->payment_method,
                'fee_percent' => $adminCommission,
                'fee_amount_usd' => $feeInUsd,
                'transfer_service_id' => $transferService?->id,

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
            // Transfer via agent
            $status = $request->agent_id ? 'in_progress' : 'pending_agent';
            
            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $destinationAmount,
                'currency' => $transactionCurrency,
                'status' => $status,
                'agent_id' => $request->agent_id ?? null,
                'service_type' => $serviceType,
                'payment_method' => $request->payment_method,
                'transfer_service_id' => $transferService?->id,
                'fee_percent' => $selectedAgent->commission ?? null,
                'fee_amount_usd' => 0,
                'fee' => $transferService ? (float) $transferService->fee : 0,
            ]);

            if ($transaction->agent_id) {
                $transaction->loadMissing('agent');

                NotificationService::sendAgentNotification(
                    $transaction->agent,
                    'New money transfer request',
                    "You have a new transfer of " . CurrencyService::format($transaction->amount, $transaction->currency ?? 'USD') . " from {$sender->name} to {$receiver->name}.",
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