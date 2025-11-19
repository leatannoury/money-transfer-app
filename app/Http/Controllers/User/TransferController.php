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
    'email' => 'nullable|email',
    'phone' => 'nullable|string',
    'service_type' => 'required|in:wallet_to_wallet,transfer_via_agent',
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
        'expiry_date' => 'required|string|date_format:m/y',
        'cvv' => 'required|string|digits:3|max:4',
    ]);
} elseif ($destinationType === 'bank') {
    $rules = array_merge($rules, [
        'account_holder_name' => 'required|string|max:255',
        'bank_name' => 'required|string|max:255',
        'account_number' => 'required|string|max:50',
        'routing_iban' => 'required|string|max:50',
    ]);
}

// 4. Run the single, consolidated validation
$request->validate($rules);

// --- END: CONSOLIDATED VALIDATION LOGIC ---

// Continue with the rest of your logic...
$sender = Auth::user();
$sender->refresh();
// ... (The amount calculation and rest of the function should follow here)

        $amount = (float) $request->amount;
        $serviceType = $request->service_type;

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
            'expiry_date' => 'required|string|date_format:m/y',
            'cvv' => 'required|string|max:4',
        ]);
    } elseif ($transferService->destination_type === 'bank') {
        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'routing_iban' => 'required|string|max:50',
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

        if (!$receiver) {
            return back()->withInput()->withErrors(['error' => 'Receiver not found.']);
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

        // If a transfer service is used, check for required recipient details
        // if ($transferService && $transferService->destination_type !== 'wallet') {
        //     if ($transferService->destination_type === 'card') {
        //         $request->validate(['recipient_card_id' => 'required|exists:fake_cards,id']);
        //         $recipientCard = FakeCard::find($request->recipient_card_id);
        //     } elseif ($transferService->destination_type === 'bank') {
        //         $request->validate(['recipient_bank_id' => 'required|exists:fake_bank_accounts,id']);
        //         $recipientBank = FakeBankAccount::find($request->recipient_bank_id);
        //     }
        // }

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
                        'expiry_date' => $request->expiry_date,
                        'cvv' => $request->cvv,
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
                        'routing_iban' => $request->routing_iban,
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
            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $destinationAmount, // Store amount in destination currency
                'currency' => $transactionCurrency,
                'status' => $transactionStatus,
                'agent_id' => $admin->id,
                'service_type' => $serviceType,
                'payment_method' => $request->payment_method,
                'transfer_service_id' => $transferService?->id,
                'fee' => $feeInUsd,
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
                'amount' => $destinationAmount,
                'currency' => $transactionCurrency,
                'status' => $status,
                'agent_id' => $request->agent_id ?? null,
                'service_type' => $serviceType,
                'payment_method' => $request->payment_method,
                'transfer_service_id' => $transferService?->id,
                'fee' => $transferService ? (float) $transferService->fee : 0,
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