@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">
@include('components.user-sidebar')

  <!-- Main Content -->
  <div class="flex-1 overflow-y-auto">
    <header class="flex justify-end items-center p-6 border-b border-gray-200 dark:border-gray-800">
      @include('components.user-notification-center')
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
    
    {{-- FIX: Add a hidden field to ensure the service's locked currency is sent to the controller --}}
    <input type="hidden" name="currency" value="{{ $selectedService->destination_currency }}">
    
    {{-- ✅ NEW: Automatically set service_type based on selected service destination_type --}}
    @if($selectedService->destination_type === 'cash_pickup')
        <input type="hidden" name="service_type" value="cash_pickup">
    @elseif(in_array($selectedService->destination_type, ['card', 'bank']))
        <input type="hidden" name="service_type" value="wallet_to_wallet">
    @else
        <input type="hidden" name="service_type" value="wallet_to_wallet">
    @endif
@endif


          {{-- Add this line to pass the destination type to JS, preferably near the top of the form --}}
@if ($selectedService)
<input type="hidden" id="selected-service-destination" value="{{ $selectedService->destination_type }}">
@endif

@php
    $isServiceSelected = isset($selectedService);

    // Hide the normal form if the service is card, bank OR cash pickup
    $isCardOrBankOrCashPickup = $isServiceSelected && in_array($selectedService->destination_type, ['card', 'bank', 'cash_pickup']);
@endphp


          <div class="space-y-6">

            {{-- This section is HIDDEN when a Card/Bank payout service is selected --}}
            <div id="normal-form-fields" @if($isCardOrBankOrCashPickup) style="display: none;" @endif>

            
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
                  <option value="cash_pickup" {{ old('service_type') == 'cash_pickup' ? 'selected' : '' }}>Cash Pickup</option>
                  </select>
                </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Wallet to Wallet: Direct transfer. Deposit/Transfer to Person & Cash Pickup: Requires agent approval.
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


              {{-- Add this after the agent selection section --}}
<div id="recipient-name-field" style="display: none;">
    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
        Recipient Name <span class="text-red-500">*</span>
    </label>
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">person</span>
        <input 
            type="text" 
            name="recipient_name" 
            id="recipient-name-input"
            placeholder="Enter recipient's full name"
            value="{{ old('recipient_name') }}"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                   rounded-lg bg-gray-50 dark:bg-gray-800 
                   focus:ring-2 focus:ring-primary 
                   text-gray-900 dark:text-white">
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
        Enter the name of the person who will collect the cash
    </p>
</div>


{{-- ALWAYS show beneficiaries for wallet_to_wallet or transfer_via_agent --}}
<div id="beneficiaries-wrapper" style="display:none;">
    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
        Select from Saved Beneficiaries
    </label>

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
                <option 
                    value="{{ $beneficiary->id }}"
                    data-phone="{{ $beneficiary->phone_number ?? '' }}"
                    data-email="{{ $beneficiary->beneficiaryUser->email ?? '' }}"
                    data-name="{{ $beneficiary->full_name }}">
                    {{ $beneficiary->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
        Select a saved beneficiary to auto-fill their information
    </p>
</div>





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

            @php
    // Restore variable so Blade does not break
    $isCardOrBankPayout = isset($selectedService)
        && in_array($selectedService->destination_type, ['card', 'bank']);
@endphp

                
             <div id="recipient-card-input-form" @if(!($isCardOrBankPayout && isset($selectedService) && $selectedService->destination_type === 'card')) style="display:none;" @endif>
    <div class="mb-4">
        <label for="cardholder_name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Cardholder Name <span class="text-red-500">*</span></label>
        <input type="text" name="cardholder_name" id="cardholder_name" value="{{ old('cardholder_name') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" />
        @error('cardholder_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="mb-4">
        <label for="card_number" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Card Number <span class="text-red-500">*</span></label>
        <input type="text" name="card_number" id="card_number" value="{{ old('card_number') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" placeholder="16 Digits" pattern="\d{16}" maxlength="16" />
        @error('card_number')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    
</div>

    <div id="recipient-bank-input-form" @if(!($isCardOrBankPayout && isset($selectedService) && $selectedService->destination_type === 'bank')) style="display:none;" @endif>
    <div class="mb-4">
        <label for="account_holder_name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Holder Name <span class="text-red-500">*</span></label>
        <input type="text" name="account_holder_name" id="account_holder_name" value="{{ old('account_holder_name') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" />
        @error('account_holder_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="mb-4">
        <label for="bank_name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Bank Name <span class="text-red-500">*</span></label>
        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" />
        @error('bank_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="mb-4">
        <label for="account_number" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Number <span class="text-red-500">*</span></label>
        <input type="text" name="account_number" id="account_number" value="{{ old('account_number') }}" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white" />
        @error('account_number')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

</div>


{{-- resources/views/user/transfer.blade.php --}}

@if ($selectedService)
    {{-- 1. Pass the selected service ID --}}
    <input type="hidden" name="transfer_service_id" value="{{ $selectedService->id }}">
    
@if ($selectedService && $selectedService->destination_type === 'cash_pickup')
<input type="hidden" name="service_type" value="cash_pickup">

    {{-- Cash Pickup Details Section --}}
    <div id="cash-pickup-fields" class="space-y-6 mb-8 p-6 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
        <h3 class="text-xl font-semibold border-b pb-3 mb-4 text-gray-800 dark:text-gray-200">
            Recipient Details & Provider
        </h3>

        {{-- Recipient Name --}}
        <div>
            <label for="recipient_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Recipient Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" id="recipient_name" name="recipient_name" 
                   value="{{ old('recipient_name') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 ">
        </div>

        {{-- Recipient Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Recipient Phone Number <span class="text-red-500">*</span>
            </label>
            <input type="number" id="phone" name="phone" 
                   value="{{ old('phone') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 ">
        </div>

        {{-- Provider Dropdown - Now Country-Specific --}}
        <div>
            <label for="provider_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Cash Pickup Provider <span class="text-red-500">*</span>
            </label>
            
            @if($providers->count() > 0)
                <select id="provider_id" name="provider_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                    <option value="" disabled selected>Select a Provider in {{ $selectedService->destination_country }}</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                            {{ $provider->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Showing providers available in {{ $selectedService->destination_country }}
                </p>
            @else
                <div class="mt-1 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        ⚠️ No providers available for {{ $selectedService->destination_country }}. Please select a different destination or contact support.
                    </p>
                </div>
                <input type="hidden" name="provider_id" value="">
            @endif
        </div>
    </div>
@endif

@endif

{{-- The main form part below this should only show the Amount/Currency/Payment Method and Submit --}}


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
      class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
{{-- Wallet should always be an option (with Lebanon specific logic if needed, but not here) --}}
        <option value="wallet" @selected(old('payment_method', 'wallet') === 'wallet')>Wallet</option>

        {{-- Saved Credit Cards should always be an option --}}
        <option value="credit_card" @selected(old('payment_method') === 'credit_card')>Saved Credit Card</option>

        {{-- Saved Bank Accounts should always be an option --}}
        <option value="bank_account" @selected(old('payment_method') === 'bank_account')>Saved Bank Account</option>
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
                    </div>
</div>

<script>
  // Get the normal fields container for conditional checks in JS
  const normalFormFields = document.getElementById('normal-form-fields');
  const isNormalFormVisible = normalFormFields && normalFormFields.style.display !== 'none';

  // Toggle between Email and Phone inputs
  document.querySelectorAll('input[name="search_type"]').forEach(radio => {
      radio.addEventListener('change', function() {
          if (!isNormalFormVisible) return;
          
          const serviceType = document.getElementById('service_type')?.value;
          if (serviceType === 'cash_pickup') return; // Don't toggle for cash pickup

          if (this.value === 'email') {
              document.getElementById('email-field').style.display = 'block';
              document.getElementById('phone-field').style.display = 'none';
          } else {
              document.getElementById('email-field').style.display = 'none';
              document.getElementById('phone-field').style.display = 'block';
          }
      });
  });

  // New function to handle cash pickup visibility
  function toggleCashPickupFields() {
      if (!isNormalFormVisible) return;
      
      const serviceTypeSelect = document.getElementById('service_type');
      if (!serviceTypeSelect) return;
      
      const serviceType = serviceTypeSelect.value;
      const isCashPickup = serviceType === 'cash_pickup';
      
      // Elements to hide for cash pickup
      const beneficiariesField = document.querySelector('#beneficiary-select')?.closest('div');
      const searchTypeRadios = document.querySelector('.flex.items-center.gap-6');
      const emailField = document.getElementById('email-field');
      
      if (isCashPickup) {
          // Hide beneficiaries, search type radios, and email field
          if (beneficiariesField) beneficiariesField.style.display = 'none';
          if (searchTypeRadios) searchTypeRadios.style.display = 'none';
          if (emailField) emailField.style.display = 'none';
          
          // Show phone field and make it required
          const phoneField = document.getElementById('phone-field');
          const phoneInput = document.getElementById('phone-input');
          if (phoneField) {
              phoneField.style.display = 'block';
              if (phoneInput) phoneInput.setAttribute('required', 'required');
          }
          
          // Update phone label to be clearer
          const phoneLabel = phoneField?.querySelector('label');
          if (phoneLabel) {
              phoneLabel.innerHTML = 'Recipient Phone Number <span class="text-red-500">*</span>';
          }
      } else {
          // Show beneficiaries, search type radios for other service types
          if (beneficiariesField) beneficiariesField.style.display = 'block';
          if (searchTypeRadios) searchTypeRadios.style.display = 'flex';
          
          // Reset phone field label
          const phoneField = document.getElementById('phone-field');
          const phoneLabel = phoneField?.querySelector('label');
          if (phoneLabel) {
              phoneLabel.innerHTML = 'Phone';
          }
          
          // Handle email/phone visibility based on search_type
          const searchTypeEmail = document.getElementById('search_type_email');
          if (searchTypeEmail?.checked) {
              if (emailField) emailField.style.display = 'block';
              if (phoneField) phoneField.style.display = 'none';
          } else {
              if (emailField) emailField.style.display = 'none';
              if (phoneField) phoneField.style.display = 'block';
          }
      }
  }

  // Updated toggleAgentSelection function
  function toggleAgentSelection() {
      if (!isNormalFormVisible) return;
      
      const serviceTypeSelect = document.getElementById('service_type');
      const agentSelection = document.getElementById('agent-selection');
      const agentIdSelect = document.getElementById('agent_id');
      const recipientNameField = document.getElementById('recipient-name-field');
      const recipientNameInput = document.getElementById('recipient-name-input');
      
      if (!serviceTypeSelect || !agentSelection || !agentIdSelect) {
          return;
      }
      
      // Show agent selection for transfer_via_agent and cash_pickup
      if (['transfer_via_agent', 'cash_pickup'].includes(serviceTypeSelect.value)) {
          agentSelection.style.display = 'block';
          agentIdSelect.setAttribute('required', 'required');
      } else {
          agentSelection.style.display = 'none';
          agentIdSelect.removeAttribute('required');
          agentIdSelect.value = '';
      }
      
      // Show recipient name field ONLY for cash_pickup
      if (serviceTypeSelect.value === 'cash_pickup') {
          recipientNameField.style.display = 'block';
          recipientNameInput.setAttribute('required', 'required');
      } else {
          recipientNameField.style.display = 'none';
          recipientNameInput.removeAttribute('required');
          recipientNameInput.value = '';
      }
      
      // Toggle cash pickup specific fields
      toggleCashPickupFields();
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

      // Initial check for Agent selection and cash pickup fields
      if (isNormalFormVisible) {
          toggleAgentSelection();
          const serviceTypeSelect = document.getElementById('service_type');
          if (serviceTypeSelect) {
              serviceTypeSelect.addEventListener('change', toggleAgentSelection);
          }
      }
  });

  // Beneficiary selection autofill logic (disable for cash pickup)
  const beneficiarySelect = document.getElementById('beneficiary-select');
  if (beneficiarySelect) {
      beneficiarySelect.addEventListener('change', function() {
          if (!isNormalFormVisible) return;
          
          const serviceType = document.getElementById('service_type')?.value;
          if (serviceType === 'cash_pickup') return; // Don't autofill for cash pickup

          const selectedOption = this.options[this.selectedIndex];
          const emailInput = document.getElementById('email-input');
          const phoneInput = document.getElementById('phone-input');
          
          if (selectedOption.value) {
              const email = selectedOption.getAttribute('data-email');
              const phone = selectedOption.getAttribute('data-phone');
              
              const searchTypeEmail = document.getElementById('search_type_email');
              const searchTypePhone = document.getElementById('search_type_phone');

              if (email) {
                  searchTypeEmail.checked = true;
                  document.getElementById('email-field').style.display = 'block';
                  document.getElementById('phone-field').style.display = 'none';
                  emailInput.value = email;
                  phoneInput.value = '';
              } else if (phone) {
                  searchTypePhone.checked = true;
                  document.getElementById('email-field').style.display = 'none';
                  document.getElementById('phone-field').style.display = 'block';
                  phoneInput.value = phone;
                  emailInput.value = '';
              } else {
                  // Default state if neither is present (shouldn't happen ideally)
                  searchTypeEmail.checked = true;
                  document.getElementById('email-field').style.display = 'block';
                  document.getElementById('phone-field').style.display = 'none';
                  emailInput.value = '';
                  phoneInput.value = '';
              }
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

function toggleBeneficiaries() {
    const serviceType = document.getElementById('service_type')?.value;
    const beneDiv = document.getElementById('beneficiaries-wrapper');

    if (!beneDiv) return;

    if (serviceType === 'wallet_to_wallet' || serviceType === 'transfer_via_agent') {
        beneDiv.style.display = 'block';
    } else {
        beneDiv.style.display = 'none';
    }
}

// Fire on load
window.addEventListener('DOMContentLoaded', toggleBeneficiaries);

// Fire when user changes service
document.getElementById('service_type')?.addEventListener('change', toggleBeneficiaries);

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

@endsection