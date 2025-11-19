@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Send Money - Transferly</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
 
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#000000",
            "background-light": "#f7f7f7",
            "background-dark": "#191919"
          },
          fontFamily: { display: "Manrope" },
        },
      },
    }
  </script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
<div class="flex h-screen">
@include('components.user-sidebar')

  <main class="flex-1 overflow-y-auto">
    <header class="flex justify-end items-center p-6 border-b border-gray-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <button class="relative text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
          <span class="material-symbols-outlined !text-2xl">notifications</span>
          <span class="absolute top-0 right-0 w-2 h-2 bg-primary rounded-full"></span>
        </button>
      </div>
    </header>

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100 text-center">
        Send Money
      </h1>



     @if($errors->any())
  <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
    <ul class="list-disc pl-5">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if(session('error'))
  <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
    {{ session('error') }}
  </div>
@endif


      @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ session('success') }}
        </div>
      @endif

      @if(isset($selectedService))
    <div class="mb-6 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Selected Service</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $selectedService->name }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $selectedService->destination_country }} • {{ ucfirst($selectedService->speed) }} •
                    {{ ucfirst($selectedService->destination_type) }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Fee: ${{ number_format($selectedService->fee, 2) }} • 
                    Rate: 1 USD = {{ number_format($selectedService->exchange_rate, 2) }} {{ $selectedService->destination_currency }}
                </p>
                @if($selectedService->promotion_active && $selectedService->promotion_text)
                    <p class="mt-1 text-sm font-medium text-green-600 dark:text-green-400">
                        {{ $selectedService->promotion_text }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif


      <div class="max-w-lg mx-auto bg-white dark:bg-zinc-900/50 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <form method="POST" action="{{ route('user.transfer.send') }}">
          @csrf

          @if(isset($selectedService))
              <input type="hidden" name="transfer_service_id" value="{{ $selectedService->id }}">
          @endif

          {{-- Add this line to pass the destination type to JS, preferably near the top of the form --}}
@if ($selectedService)
<input type="hidden" id="selected-service-destination" value="{{ $selectedService->destination_type }}">
@endif

@php
    // Check if a service is selected and its destination is a card or bank account
    $isCardOrBankPayout = isset($selectedService) && in_array($selectedService->destination_type, ['card', 'bank']);
@endphp

          <div class="space-y-6">

            {{-- This section is HIDDEN when a Card/Bank payout service is selected --}}
            <div id="normal-form-fields" @if($isCardOrBankPayout) style="display: none;" @endif>
            
              <div>
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Service</label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">handshake</span>
                  <select 
                    name="service_type" 
                    id="service_type" 
                    required
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                           rounded-lg bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-primary 
                           text-gray-900 dark:text-white">
                    <option value="wallet_to_wallet" {{ old('service_type') == 'wallet_to_wallet' ? 'selected' : '' }}>Wallet to Wallet</option>
                    <option value="transfer_via_agent" {{ old('service_type') == 'transfer_via_agent' ? 'selected' : '' }}>Deposit or Transfer to Person</option>
                  </select>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Wallet to Wallet: Direct transfer. Deposit/Transfer to Person: Requires agent approval.
                </p>
              </div>

              <div id="agent-selection" style="display: none;">
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
                  Select Agent <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">person</span>
                  <select 
                    name="agent_id" 
                    id="agent_id" 
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                           rounded-lg bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-primary 
                           text-gray-900 dark:text-white">
                    <option value="">-- Select an available agent --</option>
                    @foreach($availableAgents as $agent)
                      <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                        @if($agent->city)
                          - {{ $agent->city }}
                        @endif
                        @if($agent->phone)
                          ({{ $agent->phone }})
                        @endif
                      </option>
                    @endforeach
                  </select>
                </div>
                @error('agent_id')
                  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                @if($availableAgents->isEmpty())
                  <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">
                    ⚠️ No agents are currently available. Please try again later.
                  </p>
                @else
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Select an available agent to process your transaction.
                  </p>
                @endif
              </div>

              @if($beneficiaries->count() > 0)
              <div>
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Select from Saved Beneficiaries</label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">people</span>
                  <select 
                    id="beneficiary-select"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                           rounded-lg bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-primary 
                           text-gray-900 dark:text-white">
                    <option value="">-- Select a beneficiary (optional) --</option>
                    @foreach($beneficiaries as $beneficiary)
                      @php
                        $user = null;
                        if ($beneficiary->phone_number) {
                            $user = \App\Models\User::where('phone', $beneficiary->phone_number)->first();
                        }
                        $email = $user ? $user->email : '';
                      @endphp
                      <option 
                        value="{{ $beneficiary->id }}"
                        data-phone="{{ $beneficiary->phone_number ?? '' }}"
                        data-email="{{ $email }}"
                        data-name="{{ $beneficiary->full_name }}">
                        {{ $beneficiary->full_name }} 
                        @if($beneficiary->phone_number)
                          ({{ $beneficiary->phone_number }})
                        @endif
                      </option>
                    @endforeach
                  </select>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select a saved beneficiary to auto-fill their information</p>
              </div>
              @endif

              <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input 
                    type="radio" 
                    name="search_type" 
                    value="email"
                    id="search_type_email"
                    {{ old('search_type', 'email') == 'email' ? 'checked' : '' }}
                    class="text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  />
                  <span class="text-sm">Email</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                  <input 
                    type="radio" 
                    name="search_type" 
                    value="phone"
                    id="search_type_phone"
                    {{ old('search_type') == 'phone' ? 'checked' : '' }}
                    class="text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  />
                  <span class="text-sm">Phone</span>
                </label>
              </div>

              <div id="email-field">
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Email</label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
                  <input 
                    type="email" 
                    name="email" 
                    id="email-input"
                    placeholder="Enter receiver's email"
                    value="{{ old('email') }}"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                           rounded-lg bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-primary 
                           text-gray-900 dark:text-white">
                  @error('email')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div id="phone-field" style="display:none;">
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Phone</label>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">phone</span>
                  <input 
                    type="text" 
                    name="phone" 
                    id="phone-input"
                    placeholder="Enter receiver's phone number"
                    value="{{ old('phone') }}"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                           rounded-lg bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-primary 
                           text-gray-900 dark:text-white">
                  @error('phone')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>

            </div>
            <div id="recipient-forms-container" class="space-y-6 mt-6">
                
                <div id="recipient-card-input-form" @if(!($isCardOrBankPayout && isset($selectedService) && $selectedService->destination_type === 'card')) style="display:none;" @endif>
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 text-gray-800 dark:text-gray-200">
                        Recipient Credit Card Details
                    </h3>
                    <div class="mb-4">
                        <label for="recipient_card_nickname" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Nickname (Optional)</label>
                        <input type="text" name="recipient_card_nickname" id="recipient_card_nickname" value="{{ old('recipient_card_nickname') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" />
                    </div>
                    <div class="mb-4">
                        <label for="cardholder_name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Cardholder Name <span class="text-red-500">*</span></label>
                        <input type="text" name="cardholder_name" id="cardholder_name" value="{{ old('cardholder_name') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" required />
                        @error('cardholder_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="card_number" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Card Number <span class="text-red-500">*</span></label>
                        <input type="text" name="card_number" id="card_number" value="{{ old('card_number') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" placeholder="16 Digits" required pattern="\d{16}" maxlength="16" />
                        @error('card_number')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1 mb-4">
                            <label for="expiry_date" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Expiry Date (MM/YY) <span class="text-red-500">*</span></label>
                            <input type="text" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" placeholder="MM/YY" required pattern="(0[1-9]|1[0-2])\/([0-9]{2})" />
                            @error('expiry_date')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex-1 mb-4">
                            <label for="cvv" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">CVV <span class="text-red-500">*</span></label>
                            <input type="text" name="cvv" id="cvv" value="{{ old('cvv') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" placeholder="3 or 4 Digits" required pattern="\d{3,4}" maxlength="4" />
                            @error('cvv')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div id="recipient-bank-input-form" @if(!($isCardOrBankPayout && isset($selectedService) && $selectedService->destination_type === 'bank')) style="display:none;" @endif>
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 text-gray-800 dark:text-gray-200">
                        Recipient Bank Account Details
                    </h3>
                    <div class="mb-4">
                        <label for="recipient_bank_nickname" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Nickname (Optional)</label>
                        <input type="text" name="recipient_bank_nickname" id="recipient_bank_nickname" value="{{ old('recipient_bank_nickname') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" />
                    </div>
                    <div class="mb-4">
                        <label for="account_holder_name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Holder Name <span class="text-red-500">*</span></label>
                        <input type="text" name="account_holder_name" id="account_holder_name" value="{{ old('account_holder_name') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" required />
                        @error('account_holder_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="bank_name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Bank Name <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" required />
                        @error('bank_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="account_number" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" id="account_number" value="{{ old('account_number') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" required />
                        @error('account_number')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="routing_iban" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Routing / IBAN <span class="text-red-500">*</span></label>
                        <input type="text" name="routing_iban" id="routing_iban" value="{{ old('routing_iban') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" required />
                        @error('routing_iban')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- The following two divs were duplicated and are placeholders. They have been removed to clean up the code. --}}
            </div>





            <div>
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Currency</label>
  <div class="relative">
    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">payments</span>
    <select
      name="currency"
      id="currency"
      required
      {{ isset($selectedService) ? 'disabled' : '' }}
      class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
             rounded-lg bg-gray-50 dark:bg-gray-800 
             focus:ring-2 focus:ring-primary 
             text-gray-900 dark:text-white
             {{ isset($selectedService) ? 'opacity-60 cursor-not-allowed' : '' }}">
      @if(isset($selectedService))
        <option value="{{ $selectedService->destination_currency }}" selected>
          {{ $selectedService->destination_currency }} - Locked by service
        </option>
      @else
        @foreach($currencies as $code => $name)
          <option value="{{ $code }}" {{ old('currency', $selectedCurrency) === $code ? 'selected' : '' }}>
            {{ $code }} - {{ $name }}
          </option>
        @endforeach
      @endif
    </select>
    @if(isset($selectedService))
      <input type="hidden" name="currency" value="{{ $selectedService->destination_currency }}">
    @endif
  </div>
  @error('currency')
    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
  @enderror
  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
    @if(isset($selectedService))
      Currency is locked to {{ $selectedService->destination_currency }} for this service.
    @else
      Choose the currency you want to send in. Amount will be converted from your USD balance automatically.
    @endif
  </p>
</div>

            <div>
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
    @if(isset($selectedService))
      Amount to Send (<span id="amount-currency-label">{{ $selectedService->destination_currency }}</span>)
    @else
      Amount (<span id="amount-currency-label">{{ old('currency', $selectedCurrency) }}</span>)
    @endif
  </label>
  <div class="relative">
    <input 
      type="number" 
      step="0.01" 
      min="0.01"
      name="amount" 
      placeholder="0.00" 
      required
      value="{{ old('amount') }}"
      class="w-full pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
             rounded-lg bg-gray-50 dark:bg-gray-800 
             focus:ring-2 focus:ring-primary 
             text-gray-900 dark:text-white font-semibold">
    @error('amount')
      <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
    @enderror
  </div>
  
  @if(isset($selectedService))
    <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
      <p class="text-sm text-blue-800 dark:text-blue-200">
        <strong>Service Fee:</strong> ${{ number_format($selectedService->fee, 2) }} USD<br>
        <strong>Exchange Rate:</strong> 1 USD = {{ number_format($selectedService->exchange_rate, 2) }} {{ $selectedService->destination_currency }}
      </p>
      <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">
        Total cost will be calculated based on the exchange rate plus the service fee.
      </p>
    </div>
  @else
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
      Enter the amount you want to send. It will be deducted from your wallet balance.
    </p>
  @endif
</div>


 <div>
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
    Payment Method (Source)
  </label>
  <div class="relative">
    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">credit_card</span>
    <select 
      name="payment_method" 
      id="payment_method"
      required
      {{ isset($selectedService) ? 'disabled' : '' }}
      class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white
      {{ isset($selectedService) ? 'opacity-60 cursor-not-allowed' : '' }}"
       {{ $defaultPaymentMethod ? 'disabled' : '' }}>
       @if ($defaultPaymentMethod)
        {{-- Pre-select the method required by the selected service --}}
        <option value="{{ $defaultPaymentMethod }}" selected>
            @if ($defaultPaymentMethod === 'wallet')
                My Wallet Balance (Source)
            @elseif ($defaultPaymentMethod === 'credit_card')
                Credit/Debit Card (Source)
            @elseif ($defaultPaymentMethod === 'bank_account')
                Bank Account (Source)
            @endif
        </option>
    @else
      <option value="wallet" {{ $defaultPaymentMethod === 'wallet' ? 'selected' : '' }} {{ old('payment_method') == 'wallet' ? 'selected' : '' }}>App Wallet</option>
      <option value="credit_card" {{ $defaultPaymentMethod === 'credit_card' ? 'selected' : '' }} {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Saved Credit Card</option>
      <option value="bank_account" {{ $defaultPaymentMethod === 'bank_account' ? 'selected' : '' }} {{ old('payment_method') == 'bank_account' ? 'selected' : '' }}>Saved Bank Account</option>
    @endif
    </select>
@if(isset($selectedService) && $defaultPaymentMethod)
      <input type="hidden" name="payment_method" value="{{ $defaultPaymentMethod }}">
    @endif
  </div>

  @if(isset($selectedService))
    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
      Payment method is locked based on the selected transfer service.
    </p>
  @else
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
      Choose the method you want to use for this payment.
    </p>
  @endif
  </div>
</div>

<div id="cards-dropdown" class="mt-4 hidden">
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Select Your Card (Source)</label>
  <select name="card_id" class="w-full pl-3 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
    <option value="">-- Select a card --</option>
    @foreach($cards as $card)
      <option value="{{ $card->id }}">
        {{ $card->nickname ?? $card->provider . ' ending in ' . $card->last4 }}
      </option>
    @endforeach
  </select>
</div>

<div id="banks-dropdown" class="mt-4 hidden">
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Select Your Bank Account (Source)</label>
  <select name="bank_id" class="w-full pl-3 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
    <option value="">-- Select a bank account --</option>
    @foreach($banks as $bank)
      <option value="{{ $bank->id }}">
        {{ $bank->nickname ?? 'Bank ending in ' . $bank->last4 }}
      </option>
    @endforeach
  </select>
</div>



            <button type="submit"
              class="w-full bg-primary text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center gap-2 hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark">
              <span>Send</span>
              <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
  // Get the normal fields container for conditional checks in JS
  const normalFormFields = document.getElementById('normal-form-fields');
  const isNormalFormVisible = normalFormFields && normalFormFields.style.display !== 'none';

  // Toggle between Email and Phone inputs
  document.querySelectorAll('input[name="search_type"]').forEach(radio => {
      radio.addEventListener('change', function() {
          if (!isNormalFormVisible) return; // Exit if normal form is hidden

          if (this.value === 'email') {
              document.getElementById('email-field').style.display = 'block';
              document.getElementById('phone-field').style.display = 'none';
          } else {
              document.getElementById('email-field').style.display = 'none';
              document.getElementById('phone-field').style.display = 'block';
          }
      });
  });

  // Toggle Agent Selection based on Service Type
  function toggleAgentSelection() {
      if (!isNormalFormVisible) return; // Exit if normal form is hidden
      
      const serviceTypeSelect = document.getElementById('service_type');
      const agentSelection = document.getElementById('agent-selection');
      const agentIdSelect = document.getElementById('agent_id');
      
      if (!serviceTypeSelect || !agentSelection || !agentIdSelect) {
          return; // Elements not found, exit early
      }
      
      if (serviceTypeSelect.value === 'transfer_via_agent') {
          agentSelection.style.display = 'block';
          agentIdSelect.setAttribute('required', 'required');
      } else {
          agentSelection.style.display = 'none';
          agentIdSelect.removeAttribute('required');
          agentIdSelect.value = ''; // Clear selection
      }
  }

  // Set up event listener when DOM is ready
  window.addEventListener('DOMContentLoaded', function() {
      if (isNormalFormVisible) {
          const selectedType = "{{ old('search_type', 'email') }}";
          if (selectedType === 'phone') {
              const emailField = document.getElementById('email-field');
              const phoneField = document.getElementById('phone-field');
              if (emailField && phoneField) {
                  emailField.style.display = 'none';
                  phoneField.style.display = 'block';
              }
          }
      }

      // Initial check for Agent selection (runs only if form is visible)
      if (isNormalFormVisible) {
          toggleAgentSelection();
          const serviceTypeSelect = document.getElementById('service_type');
          if (serviceTypeSelect) {
              serviceTypeSelect.addEventListener('change', toggleAgentSelection);
          }
      }
  });


  // Beneficiary selection autofill logic
  const beneficiarySelect = document.getElementById('beneficiary-select');
  if (beneficiarySelect) {
      beneficiarySelect.addEventListener('change', function() {
          if (!isNormalFormVisible) return; // Exit if normal form is hidden

          const selectedOption = this.options[this.selectedIndex];
          const emailInput = document.getElementById('email-input');
          const phoneInput = document.getElementById('phone-input');
          
          if (selectedOption.value) {
              const email = selectedOption.getAttribute('data-email');
              const phone = selectedOption.getAttribute('data-phone');
              
              const searchTypeEmail = document.getElementById('search_type_email');
              const searchTypePhone = document.getElementById('search_type_phone');

              if (phone) {
                  // Prioritize phone if available
                  searchTypePhone.checked = true;
                  document.getElementById('email-field').style.display = 'none';
                  document.getElementById('phone-field').style.display = 'block';
                  phoneInput.value = phone;
                  emailInput.value = email || ''; // Clear or set email in hidden field
              } else if (email) {
                  // Fallback to email
                  searchTypeEmail.checked = true;
                  document.getElementById('email-field').style.display = 'block';
                  document.getElementById('phone-field').style.display = 'none';
                  emailInput.value = email;
                  phoneInput.value = ''; // Clear phone
              } else {
                  // If neither is available, clear fields and default to email search
                  searchTypeEmail.checked = true;
                  document.getElementById('email-field').style.display = 'block';
                  document.getElementById('phone-field').style.display = 'none';
                  emailInput.value = '';
                  phoneInput.value = '';
              }
          } else {
              // Option is the default "-- Select a beneficiary..."
              // Do not clear values to respect user's manual input or old('email')/old('phone')
          }
      });
  }

  // Currency label update script (Always runs)
  const currencySelect = document.getElementById('currency');
  const amountCurrencyLabel = document.getElementById('amount-currency-label');
  if (currencySelect && amountCurrencyLabel && !currencySelect.disabled) {
      const updateAmountLabel = () => {
          amountCurrencyLabel.textContent = currencySelect.value;
      };
      currencySelect.addEventListener('change', updateAmountLabel);
      currencySelect.addEventListener('input', updateAmountLabel);
  }
</script>

<script>

const paymentMethodSelect = document.getElementById('payment_method');
  const cardsDropdown = document.getElementById('cards-dropdown');
  const banksDropdown = document.getElementById('banks-dropdown');

  function togglePaymentDropdowns() {
      const selected = paymentMethodSelect.value;
      cardsDropdown.style.display = selected === 'credit_card' ? 'block' : 'none';
      banksDropdown.style.display = selected === 'bank_account' ? 'block' : 'none';
  }

  // MODIFIED: Only add the change listener if the select is NOT disabled
  if (paymentMethodSelect && !paymentMethodSelect.disabled) { 
      paymentMethodSelect.addEventListener('change', togglePaymentDropdowns);
  }

  // Initialize on page load (this remains, as it sets the initial visibility)
  window.addEventListener('DOMContentLoaded', togglePaymentDropdowns);

</script>
@if(session('msg'))

<script>
    Swal.fire({
        title: '{{ session('type') === "warning" ? "⚠ Suspicious Transaction!" : "Success" }}',
        text: "{{ session('msg') }}",
        icon: '{{ session('type') }}',
        confirmButtonText: 'Ok',
    });
</script>
@endif


</body>
</html>
@endsection