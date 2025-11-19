@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Refund Requests - Admin</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#000000",
            "background-light": "#F9FAFB",
            "background-dark": "#111827",
            "card-light": "#FFFFFF",
            "card-dark": "#1F2937",
            "border-light": "#E5E7EB",
            "border-dark": "#374151",
          },
          fontFamily: { display: ["Inter", "sans-serif"] },
        },
      },
    }
  </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
<div class="flex min-h-screen">
  @include('components.admin-sidebar')

  <main class="flex-1 flex flex-col">
    <header class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
      <h2 class="text-xl font-bold">Refund Requests</h2>
      @include('components.admin-notification-center')
    </header>

    <div class="flex-1 p-8 overflow-y-auto space-y-6">
      @if(session('success'))
        <div class="p-4 rounded-lg bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
          {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="p-4 rounded-lg bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
          {{ session('error') }}
        </div>
      @endif

      <section class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-2xl p-6">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="grid gap-4 md:grid-cols-3">
          <div>
            <label class="text-sm font-medium block mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border border-border-light dark:border-border-dark dark:bg-gray-800">
              <option value="">All</option>
              @foreach(['pending','approved','rejected','cancelled'] as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                  {{ ucfirst($status) }}
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="text-sm font-medium block mb-1">Search (ID / transaction / email)</label>
            <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-lg border border-border-light dark:border-border-dark dark:bg-gray-800" placeholder="e.g. 42"/>
          </div>
          <div class="flex items-end gap-2">
            <button class="flex-1 rounded-full bg-black text-white py-2.5 font-semibold">Filter</button>
            <a href="{{ route('admin.refunds.index') }}" class="px-4 py-2 rounded-full border border-border-light dark:border-border-dark text-sm">Reset</a>
          </div>
        </form>
      </section>

      <section class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-2xl">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 dark:text-gray-400">
                <th class="px-6 py-4">Request</th>
                <th class="px-6 py-4">Transaction</th>
                <th class="px-6 py-4">User</th>
                <th class="px-6 py-4">Amount</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Submitted</th>
                <th class="px-6 py-4">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
              @forelse($refundRequests as $requestItem)
                @php
                  $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-600',
                  ];
                @endphp
                <tr>
                  <td class="px-6 py-4">
                    <p class="font-semibold">#{{ $requestItem->id }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($requestItem->type) }}</p>
                    @if($requestItem->reason)
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Reason: {{ $requestItem->reason }}</p>
                    @endif
                    @if($requestItem->resolution_note)
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Note: {{ $requestItem->resolution_note }}</p>
                    @endif
                  </td>
                  <td class="px-6 py-4">
                    <p class="font-medium">Txn #{{ $requestItem->transaction?->id ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ ucfirst(str_replace('_',' ', $requestItem->transaction?->service_type ?? '')) }}
                    </p>
                  </td>
                  <td class="px-6 py-4">
                    <p class="font-medium">{{ $requestItem->user?->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $requestItem->user?->email }}</p>
                  </td>
                  <td class="px-6 py-4">
                    {{ $requestItem->requested_amount
                        ? \App\Services\CurrencyService::format($requestItem->requested_amount, $requestItem->currency ?? 'USD')
                        : '—' }}
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$requestItem->status] ?? 'bg-gray-100 text-gray-600' }}">
                      {{ ucfirst($requestItem->status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                    {{ $requestItem->created_at->format('M d, Y H:i') }}
                  </td>
                  <td class="px-6 py-4">
                    @if($requestItem->status === 'pending')
                      <div class="flex flex-col gap-2">
                        <form method="POST" action="{{ route('admin.refunds.decide', $requestItem) }}">
                          @csrf
                          <input type="hidden" name="action" value="approve">
                          <textarea name="note" class="w-full rounded-lg border border-border-light dark:border-border-dark dark:bg-gray-800 text-xs mb-2" placeholder="Optional note"></textarea>
                          <button class="w-full px-4 py-2 rounded-full bg-green-600 text-white text-xs font-semibold hover:bg-green-700">
                            Approve & refund
                          </button>
                        </form>
                        <form method="POST" action="{{ route('admin.refunds.decide', $requestItem) }}">
                          @csrf
                          <input type="hidden" name="action" value="reject">
                          <textarea name="note" class="w-full rounded-lg border border-border-light dark:border-border-dark dark:bg-gray-800 text-xs mb-2" placeholder="Reason for rejection"></textarea>
                          <button class="w-full px-4 py-2 rounded-full bg-red-600 text-white text-xs font-semibold hover:bg-red-700">
                            Reject
                          </button>
                        </form>
                      </div>
                    @else
                      <p class="text-xs text-gray-500 dark:text-gray-400">Resolved {{ $requestItem->resolved_at?->diffForHumans() }}</p>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    No requests found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="p-4 border-t border-border-light dark:border-border-dark">
          {{ $refundRequests->links() }}
        </div>
      </section>
    </div>
  </main>
</div>
</body>
</html>
@endsection

