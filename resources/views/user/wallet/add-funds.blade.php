@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex min-h-screen">

    @include('components.user-sidebar')

    <div class="flex-1 p-10">

        <!-- Page Title -->
        <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">
            Fund Wallet
        </h1>

        <!-- Info Card -->
        <div class="max-w-3xl bg-white dark:bg-gray-800 p-8 rounded-xl shadow 
                    border border-gray-200 dark:border-gray-700 mb-8">

            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Add Money to Your Wallet
            </h2>

            <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                You can top up your wallet balance by visiting any of our authorized Transferly agents 
                across Lebanon, or fund your wallet instantly using our secure online payment method.
            </p>

            <div class="bg-gray-100 dark:bg-gray-700/30 rounded-lg p-4 flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary dark:text-white">store</span>
                <p class="text-gray-700 dark:text-gray-300">
                    <strong>Visit an Agent:</strong> Deposit cash at any of our certified Transferly branches. 
                    The amount will be credited to your wallet immediately.
                </p>
            </div>

            <div class="bg-gray-100 dark:bg-gray-700/30 rounded-lg p-4 flex items-center gap-3">
                <span class="material-symbols-outlined text-primary dark:text-white">credit_card</span>
                <p class="text-gray-700 dark:text-gray-300">
                    <strong>Use Stripe:</strong> Instantly add funds using a debit or credit card through our 
                    secure online payment gateway.
                </p>
            </div>
        </div>

        <!-- Stripe Payment Form Card -->
        <div class="max-w-lg bg-white dark:bg-gray-800 p-8 rounded-xl shadow 
                    border border-gray-200 dark:border-gray-700">

            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary dark:text-white">bolt</span>
                Instant Funding with Stripe
            </h2>

            <form method="POST" action="{{ route('user.wallet.checkout') }}">
                @csrf

                <!-- Amount Field -->
                <label class="block mb-2 font-semibold text-gray-800 dark:text-gray-300">
                    Amount to Add (USD)
                </label>

                <input 
                    type="number" 
                    name="amount" 
                    min="1"
                    required
                    class="w-full mb-6 bg-transparent border border-gray-300 dark:border-gray-600 
                           rounded-lg p-3 text-gray-900 dark:text-white"
                    placeholder="Enter amount..."
                >

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-primary text-white dark:bg-white dark:text-primary 
                           font-bold py-3 rounded-lg flex items-center justify-center gap-2
                           hover:opacity-90 transition">
                    <span class="material-symbols-outlined text-lg">credit_score</span>
                    Fund with Stripe
                </button>

            </form>
        </div>

    </div>

</div>
@endsection
