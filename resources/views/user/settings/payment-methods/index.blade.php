@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Payment Method - Transferly</title>

  {{-- Fonts & Icons --}}
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

  {{-- Tailwind --}}
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#000000",
            "background-light": "#f7f7f7",
            "background-dark": "#191919",
          },
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

    {{-- Sidebar --}}
 @include('components.user-sidebar')

    <main class="flex-1 p-8 overflow-y-auto">

        {{-- Header --}}
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Payment Methods</h1>
            <div class="flex items-center gap-4">
                <button class="relative text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <span class="material-icons-outlined">notifications</span>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-primary rounded-full"></span>
                </button>

                         </div>
        </header>

        <div class="max-w-4xl mx-auto">

            {{-- Success message --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Add New Payment Method Button --}}
            <div class="flex justify-end mb-6">
                <a href="{{ route('user.payment-methods.create') }}"
                   class="bg-primary text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 
                           transition-colors focus:outline-none 
                          focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark dark:focus:ring-white">
                    <span class="material-icons-outlined text-base">add</span>
                    <span>Add New Payment Method</span>
                </a>
            </div>

            {{-- Payment Methods List --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">

                @if($methods->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400">No saved payment methods.</p>
                @else
                    @foreach($methods as $method)
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-8 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                    <span class="material-icons-outlined text-gray-500 dark:text-gray-400">
                                        {{ $method->type === 'credit_card' ? 'credit_card' : 'account_balance' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $method->nickname ?? $method->provider . ' ending in ' . $method->last4 }}
                                    </p>
                                    @if($method->type === 'credit_card')
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Expires {{ $method->expiry }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-4">

                                {{-- Optional: Primary badge if you track primary card --}}
                                {{-- <span class="text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 px-2.5 py-0.5 rounded-full">Primary</span> --}}

                                {{-- Delete Button --}}
                                <form action="{{ route('user.payment-methods.destroy', $method->id) }}" method="POST" onsubmit="return confirm('Delete this payment method?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-600 flex items-center gap-1">
                                        <span class="material-icons-outlined text-base">delete</span> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>
    </main>
</div>
@endsection
