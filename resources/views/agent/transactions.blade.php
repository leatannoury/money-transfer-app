@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agent Transactions - Transferly</title>

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
  <!-- Sidebar -->
  @include('components.agent-sidebar')

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
    <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
      <h1 class="text-2xl font-bold text-black dark:text-white">Agent Transactions</h1>
   <a href="{{ route('agent.dashboard') }}"
   class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium
          bg-gray-200 dark:bg-gray-800 rounded-full text-gray-800 dark:text-gray-200
          hover:bg-gray-300 dark:hover:bg-gray-700 transition">
    <span class="material-symbols-outlined text-sm">arrow_back</span>
    Dashboard
</a>



    </header>

    <div class="p-8">
      <div class="mx-auto max-w-6xl">

        {{-- Alerts --}}
        @if(session('success'))
          <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 p-4 rounded-lg mb-6">
            {{ session('success') }}
          </div>
        @elseif(session('error'))
          <div class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 p-4 rounded-lg mb-6">
            {{ session('error') }}
          </div>
        @endif

        <h2 class="text-3xl font-bold mb-6 text-black dark:text-white">Welcome, {{ $agent->name }}</h2>

        @if($transactions->isEmpty())
          <p class="text-gray-600 dark:text-gray-400 text-lg">No transactions available.</p>
        @else
          <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
              <thead class="bg-gray-100 dark:bg-gray-800 uppercase text-xs tracking-wider">
                <tr>
                  <th class="px-6 py-3">ID</th>
                  <th class="px-6 py-3">Sender</th>
                  <th class="px-6 py-3">Receiver</th>
                  <th class="px-6 py-3">Amount</th>

                  {{-- NEW COLUMN: Agent Commission --}}
                  <th class="px-6 py-3">Agent Commission</th>

                  <th class="px-6 py-3">Currency</th>
                  <th class="px-6 py-3">Status</th>
                  <th class="px-6 py-3">Action</th>
                  <th class="px-6 py-3">Created At</th>
                </tr>
              </thead>
              <tbody>
                @foreach($transactions as $tx)
                  @php
                      // Agent commission rate (e.g., 2 means 2%)
                      $commissionRate = $agent->commission ?? 0;

                      // Default commission amount
                      $commissionAmount = 0;

                      // We only show commission for meaningful statuses
                      if ($commissionRate && in_array($tx->status, ['pending_agent', 'in_progress', 'completed'])) {
                          $commissionAmount = ($tx->amount * $commissionRate) / 100;
                      }
                  @endphp

                  <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-6 py-3">{{ $tx->id }}</td>
                    <td class="px-6 py-3">{{ $tx->sender->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $tx->receiver->name ?? 'N/A' }}</td>

                    {{-- Amount --}}
                    <td class="px-6 py-3 font-semibold">
                      ${{ number_format($tx->amount, 2) }}
                    </td>

                    {{-- Agent Commission --}}
                    <td class="px-6 py-3">
                      @if($commissionRate && $tx->status !== 'failed')
                        ${{ number_format($commissionAmount, 2) }}
                        <span class="text-xs text-gray-500">
                          ({{ rtrim(rtrim(number_format($commissionRate, 2), '0'), '.') }}%)
                        </span>
                      @else
                        <span class="text-xs text-gray-400">—</span>
                      @endif
                    </td>

                    {{-- Currency --}}
                    <td class="px-6 py-3">{{ $tx->currency }}</td>

                    {{-- Status badge --}}
                    <td class="px-6 py-3">
                      <span class="
                        px-3 py-1 rounded-full text-xs font-semibold
                        @if($tx->status === 'pending_agent') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                        @elseif($tx->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($tx->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                        @elseif($tx->status === 'failed') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                        @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $tx->status)) }}
                      </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-3">
                      @if($tx->status === 'pending_agent')
                        <form action="{{ route('agent.accept', $tx->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold">
                            Accept
                          </button>
                        </form>
                      @elseif($tx->status === 'in_progress' && $tx->agent_id === $agent->id)
                        <div class="flex gap-2">
                          <form action="{{ route('agent.complete', $tx->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-semibold">
                              Complete
                            </button>
                          </form>
                          <form action="{{ route('agent.reject', $tx->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold">
                              Reject
                            </button>
                          </form>
                        </div>
                      @else
                        <button class="bg-gray-400 text-white px-4 py-2 rounded-lg text-xs font-semibold cursor-not-allowed" disabled>
                          No Action
                        </button>
                      @endif
                    </td>

                    {{-- Created at --}}
                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                      {{ $tx->created_at ? $tx->created_at->format('Y-m-d H:i') : 'N/A' }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </main>
</div>
</body>
</html>
@endsection
