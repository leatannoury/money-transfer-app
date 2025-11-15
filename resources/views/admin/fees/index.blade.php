@extends('layouts.app', ['noNav' => true])

@section('content')
<html class="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transaction Fees</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">

<div class="relative flex h-auto min-h-screen w-full flex-col">
  <div class="flex min-h-screen">

    {{-- ADMIN SIDEBAR --}}
    @include('components.admin-sidebar')

    <main class="flex-1 flex flex-col">
      
      {{-- HEADER --}}
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-xl font-bold">Wallet to Wallet Fee</h2>
      </header>

      {{-- MAIN CONTENT --}}
      <div class="p-8 max-w-xl mx-auto w-full">

        @if(session('success'))
        <div class="p-3 mb-4 rounded-lg bg-green-100 text-green-700 border border-green-300">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.fees.update') }}"
              class="bg-white dark:bg-gray-900 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 space-y-4">

          @csrf

          <div>
            <label class="text-sm font-semibold">Fee Percentage (%)</label>
            <input 
                type="number" 
                name="commission" 
                step="0.1"
                value="{{ $admin->commission }}"
                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-3 py-2"
            >
          </div>

          <button type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Save Fee
          </button>

        </form>

      </div>

    </main>
  </div>
</div>

@endsection
