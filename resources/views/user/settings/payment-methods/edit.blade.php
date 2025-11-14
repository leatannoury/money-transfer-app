@extends('layouts.app', ['noNav' => true])

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Payment Method - Transferly</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: { primary: "#000000", "background-light": "#f7f7f7", "background-dark": "#191919" },
          fontFamily: { display: "Manrope" },
          borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
        }
      }
    }
  </script>

  <style>
    .material-icons-outlined { font-size: 24px; line-height: 1; }
    input[type=text]:focus, input[type=password]:focus { --tw-ring-color: #000000; }
    .dark input[type=text]:focus, .dark input[type=password]:focus { --tw-ring-color: #ffffff; }
  </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200">

<div class="flex min-h-screen">
  
  @include('components.user-sidebar')

  <main class="flex-1 p-8 overflow-y-auto">

    @php
        $methodType = $method->type === 'bank_account' ? 'bank' : 'card';
    @endphp

    <header class="flex justify-between items-center mb-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('user.payment-methods.index') }}" 
           class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
          <span class="material-icons-outlined">arrow_back</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ $methodType === 'bank' ? 'Edit Bank Account' : 'Edit Credit Card' }}
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

        <form method="POST" action="{{ route('user.payment-methods.update', $method->id) }}" class="space-y-6">
          @csrf
          @method('PUT')

          @if($methodType === 'bank')
              <input type="hidden" name="method_type" value="bank_account">

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nickname (Optional)</label>
                <input type="text" name="nickname" value="{{ old('nickname', $method->nickname) }}" placeholder="e.g. My checking account"
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Holder Name</label>
                <input type="text" name="account_holder" value="{{ old('account_holder', $method->cardholder_name) }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $method->provider) }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number</label>
                <input type="text" name="account_number" value="{{ old('account_number', $method->card_mask) }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Routing / IBAN</label>
                <input type="text" name="routing" value="{{ old('routing', $method->details['routing'] ?? '') }}" 
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500">
              </div>

          @else
              {{-- Credit Card Form --}}
              <input type="hidden" name="method_type" value="credit_card">

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nickname (Optional)</label>
                <input type="text" name="nickname" value="{{ old('nickname', $method->nickname) }}" 
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                         focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                         text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                  placeholder="e.g. My primary card">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cardholder Name</label>
                <input type="text" name="cardholder_name" value="{{ old('cardholder_name', $method->cardholder_name) }}" required
                  class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                         focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                         text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"
                  placeholder="Enter name as it appears on card">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Number</label>
                <div class="relative">
                  <input type="text" name="card_number" value="{{ old('card_number', $method->card_mask) }}" required
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
                  <input type="text" name="expiry" value="{{ old('expiry', $method->expiry) }}" required
                    class="w-full bg-transparent border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                           focus:border-primary dark:focus:border-white focus:ring-primary dark:focus:ring-white 
                           text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"
                    placeholder="MM/YY">
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CVV</label>
                  <div class="relative">
                    <input type="password" name="cvv" maxlength="4"
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
              {{ $methodType === 'bank' ? 'Update Bank Account' : 'Update Card' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
@endsection
