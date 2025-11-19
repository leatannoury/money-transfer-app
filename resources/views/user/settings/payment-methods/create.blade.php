@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex min-h-screen">
  
  {{-- Sidebar --}}
  @include('components.user-sidebar')

  {{-- Main Content --}}
  <div class="flex-1 p-8 overflow-y-auto">

    @php
        $methodType = request()->query('type', 'card'); // default to card
    @endphp

    <header class="flex justify-between items-center mb-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('user.payment-methods.index') }}" 
           class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
          <span class="material-icons-outlined">arrow_back</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ $methodType === 'bank' ? 'Add New Bank Account' : 'Add New Credit Card' }}
        </h1>
      </div>
    </header>

    <div class="max-w-2xl mx-auto">
      <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">

        {{-- Validation Errors --}}
        @if($errors->any())
          <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            <ul class="list-disc ml-5">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('user.payment-methods.store') }}" class="space-y-6">
          @csrf

          @if($methodType === 'bank')
              <input type="hidden" name="method_type" value="bank_account">

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nickname (Optional)</label>
                <input type="text" name="nickname" value="{{ old('nickname') }}" placeholder="e.g. My checking account"
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Holder Name</label>
                <input type="text" name="account_holder" value="{{ old('account_holder') }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number</label>
                <input type="text" name="account_number" value="{{ old('account_number') }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Routing / IBAN</label>
                <input type="text" name="routing" value="{{ old('routing') }}" 
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

          @else
              {{-- Credit Card Form --}}
              <input type="hidden" name="method_type" value="credit_card">

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nickname (Optional)</label>
                <input type="text" name="nickname" value="{{ old('nickname') }}" 
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                         focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                         text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                  placeholder="e.g. My primary card">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cardholder Name</label>
                <input type="text" name="cardholder_name" value="{{ old('cardholder_name') }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                         focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                         text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"
                  placeholder="Enter name as it appears on card">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Number</label>
                <div class="relative">
                  <input type="text" name="card_number" value="{{ old('card_number') }}" required
                    class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                           focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                           pl-10 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"
                    placeholder="0000 0000 0000 0000">
                  <span class="material-icons-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
                    credit_card
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
                  <input type="text" name="expiry" value="{{ old('expiry') }}" required
                    class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                           focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                           text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"
                    placeholder="MM/YY">
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CVV</label>
                  <div class="relative">
                    <input type="password" name="cvv" maxlength="4" required
                      class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                             focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                             text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"
                      placeholder="123">
                    <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base">
                      help_outline
                    </span>
                  </div>
                </div>
              </div>
          @endif

          <div class="flex justify-end pt-4">
            <button type="submit"
              class="w-full bg-primary text-white dark:bg-white dark:text-primary font-bold py-3 px-6 rounded-lg 
                     hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary 
                     dark:focus:ring-offset-background-dark dark:focus:ring-white transition-opacity">
              {{ $methodType === 'bank' ? 'Add Bank Account' : 'Add Card' }}
            </button>
          </div>
        </form>
      </div>
    </div>
</div>
</div>
@endsection
