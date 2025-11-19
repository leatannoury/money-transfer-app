@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">
    @include('components.agent-sidebar')

    <div class="flex-1 overflow-y-auto">
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
            <div>
                <h1 class="text-2xl font-bold text-black dark:text-white">Cash-In</h1>
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
                    <h2 class="text-xl font-bold text-black dark:text-white mb-6">Cash-In Form</h2>

                    <form action="{{ route('agent.cash.in') }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Identify user by --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Identify user by</label>
                            <select id="ci_search_type" name="search_type"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2">
                                <option value="email" {{ old('search_type','email') === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="phone" {{ old('search_type') === 'phone' ? 'selected' : '' }}>Phone</option>
                            </select>
                            @error('search_type')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email or Phone --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Email or Phone</label>
                            <input id="ci_email_or_phone"
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
                            Commission <strong>0.5%</strong> will be taken from the user.
                        </p>

                        <button type="submit"
                                class="mt-2 bg-black dark:bg-white dark:text-black text-white font-bold py-3 px-6 rounded-full hover:opacity-80 transition-opacity">
                            Confirm Cash-In
                        </button>
                    </form>
                </div>
            </div>
        </div>
</div>
</div>

<script>
    // Update placeholder based on search type
    const ciType = document.getElementById('ci_search_type');
    const ciInput = document.getElementById('ci_email_or_phone');

    function updateCiPlaceholder() {
        if (ciType.value === 'email') {
            ciInput.placeholder = 'user@gmail.com';
        } else {
            ciInput.placeholder = '03 123 456';
        }
    }

    if (ciType && ciInput) {
        ciType.addEventListener('change', updateCiPlaceholder);
        updateCiPlaceholder();
    }
</script>

@endsection
