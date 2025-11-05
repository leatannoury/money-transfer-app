@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - Transferly</title>
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
<main class="flex-1">
<header class="flex h-20 items-center justify-end border-b border-[#CCCCCC] px-8 dark:border-white/20">
<div class="flex items-center gap-6">
<div class="relative">
<span class="material-symbols-outlined text-black dark:text-white text-2xl cursor-pointer">notifications</span>
<div class="absolute -top-1 -right-1 size-2 rounded-full bg-black dark:bg-white"></div>
</div>
</div>
</header>

<div class="p-8">
<div class="mx-auto max-w-4xl">

<!-- Welcome -->
<div class="flex flex-wrap items-center justify-between gap-3">
<p class="text-black dark:text-white text-4xl font-black leading-tight tracking-tighter">
    Welcome back, {{ $user->name }}
</p>
</div>

<!-- Balance Display -->
<div class="mt-8 flex flex-col gap-6 rounded-xl border border-[#CCCCCC] p-8 dark:border-white/20">
<h1 class="text-black/60 dark:text-white/60 tracking-normal text-base font-medium text-left">Total Balance</h1>
<p class="text-black dark:text-white text-5xl font-black tracking-tighter -mt-4">${{ number_format($user->balance, 2) }}</p>

<!-- Actions -->
<div class="flex flex-1 gap-4 flex-wrap justify-start mt-2">
<a href="{{ route('user.transfer') }}" class="flex min-w-[84px] items-center justify-center gap-2 rounded-full h-12 px-6 bg-black text-white text-base font-bold hover:opacity-80 dark:bg-white dark:text-black">
    <span class="material-symbols-outlined">north_east</span>
    <span>Send Money</span>
</a>
<a href="{{ route('user.transactions') }}" class="flex min-w-[84px] items-center justify-center gap-2 rounded-full h-12 px-6 border border-black text-black text-base font-bold hover:bg-black/5 dark:border-white dark:text-white dark:hover:bg-white/10">
    <span class="material-symbols-outlined">receipt_long</span>
    <span>View History</span>
</a>
</div>
</div>

<!-- Recent Transactions -->
<div class="mt-10">
<div class="flex items-center justify-between">
<h2 class="text-black dark:text-white text-[22px] font-bold leading-tight tracking-tight">Recent Transactions</h2>
<a href="{{ route('user.transactions') }}" class="text-black dark:text-white text-sm font-bold underline">View All</a>
</div>

<div class="mt-5 flow-root">
<div class="divide-y divide-[#CCCCCC] dark:divide-white/20">

@forelse($transactions as $txn)
<div class="flex items-center justify-between gap-4 py-4">
    <div class="flex items-center gap-4">
        <div class="flex size-10 items-center justify-center rounded-full {{ $txn->sender_id == $user->id ? 'bg-black/5 dark:bg-white/10' : 'bg-green-100 dark:bg-green-900/30' }}">
            <span class="material-symbols-outlined text-black dark:text-white">
                {{ $txn->sender_id == $user->id ? 'call_made' : 'call_received' }}
            </span>
        </div>
        <div>
            <p class="font-bold text-black dark:text-white">
                {{ $txn->sender_id == $user->id ? 'Sent to ' . $txn->receiver->name : 'Received from ' . $txn->sender->name }}
            </p>
            <p class="text-sm text-black/60 dark:text-white/60">{{ $txn->created_at->format('M d, Y') }}</p>
        </div>
    </div>
    <div class="text-right">
        <p class="font-bold text-black dark:text-white">
            {{ $txn->sender_id == $user->id ? '-' : '+' }}${{ number_format($txn->amount, 2) }}
        </p>
        <p class="text-sm {{ $txn->status == 'pending' ? 'text-yellow-600 dark:text-yellow-400' : 'text-black/60 dark:text-white/60' }}">
            {{ ucfirst($txn->status) }}
        </p>
    </div>
</div>
@empty
    <p class="text-black/60 dark:text-white/60 mt-4">No transactions found.</p>
@endforelse

</div>
</div>
</div>
</div>
</div>
</main>
</div>
</div>
</body>
</html>
@endsection
