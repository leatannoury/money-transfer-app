@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transactions - Transferly</title>
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
  <!-- Sidebar -->
  <aside class="w-64 bg-background-light dark:bg-background-dark p-6 flex flex-col justify-between border-r border-gray-200 dark:border-gray-800">
    <div>
      <div class="flex items-center gap-3 mb-12">
        <div class="w-8 h-8 bg-primary rounded-full"></div>
        <span class="font-bold text-xl">Transferly</span>
      </div>
      <nav class="flex flex-col gap-2">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">dashboard</span>
          <span>Dashboard</span>
        </a>
        <a href="{{ route('user.transactions') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">receipt_long</span>
          <span>Transactions</span>
        </a>
        <a href="{{ route('user.transfer') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">north_east</span>
          <span>Send Money</span>
        </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
<span class="material-symbols-outlined">settings</span>
<p >Settings</p>
</a>
      </nav>
    </div>

    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
      <div>
        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
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
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100">All Transactions</h1>

      <div class="bg-white dark:bg-zinc-900/50 p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Transaction History</h2>
          <div class="flex items-center gap-4">
            <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
              <span class="material-symbols-outlined !text-base">filter_list</span> Filter
            </button>
            <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
              <span class="material-symbols-outlined !text-base">download</span> Export
            </button>
          </div>
        </div>

        <div class="space-y-2">
          @forelse($transactions as $txn)
            <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-800">
              <!-- Icon -->
              <div class="w-10 h-10 rounded-full 
                @if($txn->sender_id == Auth::id())
                  bg-gray-100 dark:bg-gray-800
                @else
                  bg-green-100 dark:bg-green-900/50
                @endif
                flex items-center justify-center mr-4">
                <span class="material-symbols-outlined
                  @if($txn->sender_id == Auth::id())
                    text-gray-600 dark:text-gray-400
                  @else
                    text-green-600 dark:text-green-400
                  @endif">
                  {{ $txn->sender_id == Auth::id() ? 'north_east' : 'south_west' }}
                </span>
              </div>

              <!-- Details -->
              <div class="flex-1">
                @if($txn->sender_id == Auth::id())
                  <p class="font-semibold text-gray-900 dark:text-gray-100">Sent to {{ $txn->receiver->name }}</p>
                @else
                  <p class="font-semibold text-gray-900 dark:text-gray-100">Received from {{ $txn->sender->name }}</p>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $txn->created_at->format('M d, Y H:i') }}</p>
              </div>

              <!-- Amount & Status -->
              <div class="text-right">
                <p class="font-semibold text-gray-900 dark:text-gray-100">
                  {{ $txn->sender_id == Auth::id() ? '-' : '+' }}${{ number_format($txn->amount, 2) }}
                </p>
                <p class="text-sm 
                  @if($txn->status == 'completed') text-green-600 dark:text-green-500
                  @elseif($txn->status == 'pending') text-yellow-500
                  @else text-red-500
                  @endif">
                  {{ ucfirst($txn->status) }}
                </p>
              </div>
            </div>
          @empty
            <p class="text-center text-gray-500 dark:text-gray-400 py-6">No transactions yet.</p>
          @endforelse
        </div>
      </div>
    </div>
  </main>
</div>
</body>
</html>
@endsection
