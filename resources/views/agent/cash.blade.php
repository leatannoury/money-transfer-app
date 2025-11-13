@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cash Operations - Transferly</title>

    {{-- Tailwind & Fonts --}}
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
    {{-- Sidebar --}}
    @include('components.agent-sidebar')

    {{-- Main Content --}}
    <main class="flex-1 overflow-y-auto">
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
            <div>
                <h1 class="text-2xl font-bold text-black dark:text-white">Cash Operations</h1>
                @isset($agent)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Logged in as: <span class="font-semibold">{{ $agent->name }}</span>
                    </p>
                @endisset
            </div>

            <a href="{{ route('agent.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Back to Dashboard</span>
            </a>
        </header>

        <div class="p-8">
            <div class="mx-auto max-w-4xl space-y-8">

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 p-4 rounded-lg">
                        {{ session('success') }}
                    </div>
                @elseif($errors->any())
                    <div class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 p-4 rounded-lg">
                        <ul class="list-disc pl-5 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Cash-In Card --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-black dark:text-white">Cash-In</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Add physical cash to a user's wallet
                        </span>
                    </div>

<form action="{{ route('agent.cash.in') }}" method="POST" class="space-y-4">
    @csrf

    <div class="grid md:grid-cols-2 gap-4">
        {{-- Identify user by --}}
        <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Identify user by</label>
            <select name="search_type"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
                <option value="">Choose…</option>
                <option value="email" {{ old('search_type') === 'email' ? 'selected' : '' }}>Email</option>
                <option value="phone" {{ old('search_type') === 'phone' ? 'selected' : '' }}>Phone</option>
            </select>
            @error('search_type')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email or Phone --}}
        <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Email or Phone</label>

            {{-- We always send BOTH, controller will use the right one --}}
            <input type="text"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="user@gmail.com"
                   class="w-full mb-2 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
            @error('email')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror

            <input type="text"
                   name="phone"
                   value="{{ old('phone') }}"
                   placeholder="03 123 456"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
            @error('phone')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Amount --}}
    <div>
        <label class="block text-gray-700 dark:text-gray-300 mb-2">Amount (USD)</label>
        <input type="number" step="0.01" min="1" name="amount"
               value="{{ old('amount') }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
        @error('amount')
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        The user is depositing this amount as cash. A 0.5% commission will be taken from their wallet and added to your balance.
    </p>

    <button type="submit"
            class="mt-2 bg-black dark:bg-white dark:text-black text-white font-bold py-3 px-6 rounded-full hover:opacity-80 transition-opacity">
        Confirm Cash-In
    </button>
</form>


                </div>

             {{-- Cash-Out --}}
<div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 mt-8 shadow-sm">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold text-black dark:text-white">Cash-Out</h2>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Withdraw from user wallet and give cash
      </p>
    </div>
  </div>

  <form action="{{ route('agent.cash.out') }}" method="POST" class="space-y-4">
    @csrf

    <div class="grid md:grid-cols-2 gap-4">
      {{-- Identify user by --}}
      <div>
        <label class="block text-gray-700 dark:text-gray-300 mb-2">Identify user by</label>
        <select name="search_type_out"
                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
          <option value="">Choose…</option>
          <option value="email" {{ old('search_type_out') === 'email' ? 'selected' : '' }}>Email</option>
          <option value="phone" {{ old('search_type_out') === 'phone' ? 'selected' : '' }}>Phone</option>
        </select>
        @error('search_type_out')
          <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Email or Phone --}}
      <div>
        <label class="block text-gray-700 dark:text-gray-300 mb-2">Email or Phone</label>
        <input type="text"
               name="{{ old('search_type_out', 'email') === 'phone' ? 'phone_out' : 'email_out' }}"
               value="{{ old('email_out') ?? old('phone_out') }}"
               placeholder="user@gmail.com or 03 123 456"
               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
        @error('email_out')
          <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
        @error('phone_out')
          <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
      </div>
    </div>

    {{-- Amount --}}
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-2">Amount (USD)</label>
      <input type="number" step="0.01" min="1" name="amount_out"
             value="{{ old('amount_out') }}"
             class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
      @error('amount_out')
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
      The amount will be deducted from the user’s wallet. You must give the user the equivalent cash.
    </p>

    <button type="submit"
            class="mt-2 bg-black dark:bg-white dark:text-black text-white font-bold py-3 px-6 rounded-full hover:opacity-80 transition-opacity">
      Confirm Cash-Out
    </button>
  </form>
</div>


            </div>
        </div>
    </main>
</div>
</body>
</html>
@endsection
