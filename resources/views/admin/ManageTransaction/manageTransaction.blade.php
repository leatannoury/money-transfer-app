@extends('layouts.app', ['noNav' => true])

@section('content')
<html class="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Agents</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": { DEFAULT: "#3B82F6" },
            "background-light": "#F9FAFB",
            "background-dark": "#111827",
            "card-light": "#FFFFFF",
            "card-dark": "#1F2937",
            "text-light": "#1F2937",
            "text-dark": "#F9FAFB",
            "border-light": "#E5E7EB",
            "border-dark": "#374151",
            "success": "#10B981",
            "warning": "#F59E0B",
            "error": "#EF4444",
          },
          fontFamily: { display: ["Inter", "sans-serif"] },
          borderRadius: { "xl": "1rem" }
        }
      }
    }
  </script>

  <style>
    .material-symbols-outlined {
      font-variation-settings:
      'FILL' 0,
      'wght' 400,
      'GRAD' 0,
      'opsz' 24
    }
  </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark flex flex-col">
      <div class="p-6 flex items-center gap-3 border-b border-border-light dark:border-border-dark">
        <div class="bg-primary text-white p-2 rounded-lg">
          <span class="material-symbols-outlined">dashboard</span>
        </div>
        <h1 class="text-lg font-bold">Admin Panel</h1>
      </div>

      <nav class="flex-grow p-4">
        <ul class="flex flex-col gap-2">
          <li>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">grid_view</span>
              <span class="text-sm font-medium">Dashboard</span>
            </a>
          </li>
          <li>
            <a href="{{ route('admin.users') }}"class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">group</span>
              <span class="text-sm font-medium">Users</span>
            </a>
          </li>
          <li>
            <a href="{{ route('admin.agents') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">support_agent</span>
              <span class="text-sm font-semibold">Agents</span>
            </a>
          </li>
          <li>
            <a href="{{route('admin.transactions')}}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/20 text-primary">
              <span class="material-symbols-outlined">receipt_long</span>
              <span class="text-sm font-medium">Transactions</span>
            </a>
          </li>
        </ul>
      </nav>

      <div class="p-4 border-t border-border-light dark:border-border-dark">
       <div class="flex items-center gap-3">
  <!-- Avatar -->
  <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>

  <!-- User Info and Logout -->
  <div class="flex flex-col gap-2">
    <div>
      <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
        <img src="{{ asset('images/logout-svgrepo-com.svg') }}" class="h-5 w-5" alt="Logout Icon" />

        <span class="text-sm font-medium">Log Out</span>
      </button>
    </form>
  </div>
</div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-xl font-bold">Transaction List</h2>
      </header>
<div class="p-8">
    

    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-3">Sender Email</th>
                    <th class="px-6 py-3">Receiver Email</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Currency</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <td class="px-6 py-4">{{ $transaction->sender->email ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $transaction->receiver->email ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ number_format($transaction->amount, 2) }}</td>
                        <td class="px-6 py-4">{{ $transaction->currency }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'completed' => 'text-green-600 bg-green-100',
                                    'pending' => 'text-yellow-600 bg-yellow-100',
                                    'failed' => 'text-red-600 bg-red-100',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$transaction->status] ?? 'text-gray-600 bg-gray-100' }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
