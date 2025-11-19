@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Refund Requests - Transferly</title>
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
  @include('components.user-sidebar')

  <main class="flex-1 overflow-y-auto">
    <header class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-800">
      <h1 class="text-3xl font-bold">Refund Requests</h1>
      @include('components.user-notification-center')
    </header>

    <div class="p-8 space-y-8 max-w-6xl mx-auto">
      @if(session('success'))
        <div class="p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
          {{ session('error') }}
        </div>
      @endif

      <section class="bg-white dark:bg-zinc-900/50 p-6 rounded-2xl border border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3 mb-4">
          <span class="material-symbols-outlined text-2xl">info</span>
          <div>
            <h2 class="text-xl font-semibold">How it works</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Submit a refund for:</p>
          </div>
        </div>
        <ul class="list-disc pl-6 text-sm text-gray-600 dark:text-gray-300 space-y-2">
          <li>Wallet-to-wallet transfers that are already completed.</li>
          <li>Wallet-to-person transfers (via agent) that are currently in progress.</li>
          <li>You can only have one pending request per transaction and a reason is required.</li>
        </ul>
      </section>

      <section class="bg-white dark:bg-zinc-900/50 p-6 rounded-2xl border border-gray-200 dark:border-gray-800">
        <h2 class="text-xl font-semibold mb-4">Submit a request</h2>

        @if($eligibleTransactions->isEmpty())
          <p class="text-sm text-gray-500 dark:text-gray-400">No eligible transactions found. Only completed wallet-to-wallet transfers or wallet-to-person transfers that are still in progress appear here.</p>
        @else
          @php
            $selectedTransactionId = old('transaction_id', $prefillTransactionId ?? null);
          @endphp
          <form method="POST" action="{{ route('user.refunds.store') }}" class="space-y-4">
            @csrf
            <div>
              <label class="block text-sm font-medium mb-1">Transaction</label>
              <select name="transaction_id" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                @foreach($eligibleTransactions as $txn)
                  <option value="{{ $txn->id }}" {{ (int) $selectedTransactionId === $txn->id ? 'selected' : '' }}>
                    #{{ $txn->id }} • {{ $txn->receiver?->name ?? 'Unknown' }} • {{ \App\Services\CurrencyService::format($txn->amount, $txn->currency ?? 'USD') }} ({{ str_replace('_', ' ', ucfirst($txn->service_type)) }})
                  </option>
                @endforeach
              </select>
            </div>
              <div>
              <label class="block text-sm font-medium mb-1">Reason <span class="text-red-500">*</span></label>
              <textarea name="reason" rows="3" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Explain why you need this refund.">{{ old('reason') }}</textarea>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-black text-white font-semibold hover:bg-gray-900">
              <span class="material-symbols-outlined text-base">send</span>
              Submit request
            </button>
          </form>
        @endif
      </section>

      <section class="bg-white dark:bg-zinc-900/50 p-6 rounded-2xl border border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">My requests</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 dark:text-gray-400">
                <th class="py-3">ID</th>
                <th class="py-3">Transaction</th>
                <th class="py-3">Type</th>
                <th class="py-3">Amount</th>
                <th class="py-3">Status</th>
                <th class="py-3">Updated</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
              @forelse($requests as $requestItem)
                @php
                  $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-600',
                  ];
                @endphp
                <tr>
                  <td class="py-3 font-semibold">#{{ $requestItem->id }}</td>
                  <td class="py-3">
                    <p class="font-medium text-gray-900 dark:text-gray-100">#{{ $requestItem->transaction?->id ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ $requestItem->transaction?->receiver?->name ?? 'Unknown receiver' }}
                    </p>
                  </td>
                  <td class="py-3 capitalize">{{ $requestItem->type }}</td>
                  <td class="py-3">
                    {{ $requestItem->requested_amount
                        ? \App\Services\CurrencyService::format($requestItem->requested_amount, $requestItem->currency ?? 'USD')
                        : '—' }}
                  </td>
                  <td class="py-3">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$requestItem->status] ?? 'bg-gray-100 text-gray-600' }}">
                      {{ ucfirst($requestItem->status) }}
                    </span>
                  </td>
                  <td class="py-3 text-gray-500 dark:text-gray-400">
                    {{ $requestItem->updated_at->diffForHumans() }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="py-6 text-center text-gray-500 dark:text-gray-400">
                    No requests yet.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $requests->links() }}
        </div>
      </section>
    </div>
  </main>
</div>
</body>
</html>
@endsection

