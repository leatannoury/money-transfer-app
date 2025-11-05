@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Money Transfer App</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#000000",
                        "background-light": "#ffffff",
                        "background-dark": "#000000",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0rem",
                        "lg": "0rem",
                        "xl": "0rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Manrope', sans-serif;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="flex min-h-screen w-full flex-col">
<div class="flex flex-1">
<!-- Side Navigation -->
<aside class="flex w-64 flex-col border-r border-[#CCCCCC] bg-background-light dark:bg-background-dark dark:border-white/20">
<div class="flex h-full flex-col justify-between p-6">
<div class="flex flex-col gap-8">
<div class="flex items-center gap-3">
<div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 bg-black dark:bg-white"></div>
<h1 class="text-black dark:text-white text-lg font-bold leading-normal">Transferly</h1>
</div>
<nav class="flex flex-col gap-2">
<a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-black dark:text-white bg-black/10 dark:bg-white/20">
<span class="material-symbols-outlined text-black dark:text-white text-2xl">grid_view</span>
<p class="text-black dark:text-white text-sm font-bold leading-normal">Dashboard</p>
</a>
<a href="{{ route('user.transactions') }}" class="flex items-center gap-3 px-3 py-2 text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/10 rounded-lg">
<span class="material-symbols-outlined text-black dark:text-white text-2xl font-light">receipt_long</span>
<p class="text-black dark:text-white text-sm font-medium leading-normal">Transactions</p>
</a>
<a href="{{ route('user.transfer') }}" class="flex items-center gap-3 px-3 py-2 text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/10 rounded-lg">
<span class="material-symbols-outlined text-black dark:text-white text-2xl font-light">north_east</span>
<p class="text-black dark:text-white text-sm font-medium leading-normal">Send Money</p>
</a>
<a href="#" class="flex items-center gap-3 px-3 py-2 text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/10 rounded-lg">
<span class="material-symbols-outlined text-black dark:text-white text-2xl font-light">settings</span>
<p class="text-black dark:text-white text-sm font-medium leading-normal">Settings</p>
</a>
</nav>
</div>
<div class="flex items-center gap-3">
<div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random');"></div>
<div class="flex flex-col">
<p class="text-black dark:text-white text-base font-bold leading-normal">{{ $user->name }}</p>
<p class="text-black/60 dark:text-white/60 text-sm font-normal leading-normal">{{ $user->email }}</p>
</div>
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
