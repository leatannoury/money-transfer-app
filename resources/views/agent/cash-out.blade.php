@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cash-Out - Transferly</title>

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
    @include('components.agent-sidebar')

    <main class="flex-1 overflow-y-auto">
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
            <div>
                <h1 class="text-2xl font-bold text-black dark:text-white">Cash-Out</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Logged in as: <span class="font-semibold">{{ auth()->user()->name }}</span>
                </p>
            </div>

            <a href="{{ route('agent.cash.menu') }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Back</span>
            </a>
        </header>

        <div class="p-8">
            <div class="mx-auto max-w-3xl">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                        {{ session('success') }}
                    </div>
                @elseif($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
                        <ul class="list-disc pl-5 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-black dark:text-white mb-6">Cash-Out Form</h2>

                    <form action="{{ route('agent.cash.out') }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Identify user by --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Identify user by</label>
                            <select id="co_search_type" name="search_type"
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
                            <input id="co_email_or_phone"
                                   type="text"
                                   name="email_or_phone"
                                   value="{{ old('email_or_phone') }}"
                                   placeholder="user@gmail.com or 03 123 456"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
                            @error('email_or_phone')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
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
                            The user will be charged <strong>amount + 0.5% commission</strong>.  
                            You must give the user the full cash amount.
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

<script>
    const coType  = document.getElementById('co_search_type');
    const coInput = document.getElementById('co_email_or_phone');

    function updateCoPlaceholder() {
        if (!coType.value || coType.value === 'email') {
            coInput.placeholder = 'user@gmail.com';
        } else {
            coInput.placeholder = '03 123 456';
        }
    }

    if (coType && coInput) {
        coType.addEventListener('change', updateCoPlaceholder);
        updateCoPlaceholder();
    }
</script>
</body>
</html>
@endsection
