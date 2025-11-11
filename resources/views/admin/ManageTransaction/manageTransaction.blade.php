@extends('layouts.app', ['noNav' => true])

@section('content')
<html class="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Transactions</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
<div class="relative flex h-auto min-h-screen w-full flex-col">
  <div class="flex min-h-screen">
    @include('components.admin-sidebar')

    <main class="flex-1 flex flex-col">
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-xl font-bold">Transaction List</h2>
      </header>

      <div class="p-8 space-y-10">

        {{-- WALLET TO WALLET --}}
        <div>
          <h3 class="text-lg font-semibold mb-4">Wallet to Wallet Transactions</h3>
          <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
         <form method="GET" action="{{ url()->current() }}" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-4 max-w-6xl mx-auto justify-between items-end">

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Email</label>
    <input type="text" name="wallet_email" value="{{ request('wallet_email') }}" placeholder="example@email.com"
           class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Status</label>
    <select name="wallet_status" class="mt-1 block w-full rounded-md border-gray-300 text-center">
      <option value="">All</option>
      <option value="completed" {{ request('wallet_status') == 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="pending" {{ request('wallet_status') == 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="failed" {{ request('wallet_status') == 'failed' ? 'selected' : '' }}>Failed</option>
    </select>
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">From Date</label>
    <input type="date" name="wallet_from_date" value="{{ request('wallet_from_date') }}" class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">To Date</label>
    <input type="date" name="wallet_to_date" value="{{ request('wallet_to_date') }}" class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Sort</label>
    <select name="wallet_sort" class="mt-1 block w-full rounded-md border-gray-300 text-center">
      <option value="">Newest</option>
      <option value="oldest" {{ request('wallet_sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
      <option value="amount_desc" {{ request('wallet_sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High → Low)</option>
      <option value="amount_asc" {{ request('wallet_sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low → High)</option>
    </select>
  </div>

  <div class="flex items-end justify-center">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
  </div>

</form>



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
                @forelse($walletToWallet as $transaction)
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
                      No wallet-to-wallet transactions found.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- WALLET TO PERSON --}}
        <div>
          <h3 class="text-lg font-semibold mb-4">Wallet to Person Transactions</h3>
          <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
    <form method="GET" action="{{ url()->current() }}" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-4 max-w-6xl mx-auto justify-between items-end">

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Email</label>
    <input type="text" name="person_email" value="{{ request('person_email') }}" placeholder="example@email.com"
           class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Status</label>
    <select name="person_status" class="mt-1 block w-full rounded-md border-gray-300 text-center">
      <option value="">All</option>
      <option value="completed" {{ request('person_status') == 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="pending" {{ request('person_status') == 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="failed" {{ request('person_status') == 'failed' ? 'selected' : '' }}>Failed</option>
    </select>
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">From Date</label>
    <input type="date" name="person_from_date" value="{{ request('person_from_date') }}" class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">To Date</label>
    <input type="date" name="person_to_date" value="{{ request('person_to_date') }}" class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Sort</label>
    <select name="person_sort" class="mt-1 block w-full rounded-md border-gray-300 text-center">
      <option value="">Newest</option>
      <option value="oldest" {{ request('person_sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
      <option value="amount_desc" {{ request('person_sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High → Low)</option>
      <option value="amount_asc" {{ request('person_sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low → High)</option>
    </select>
  </div>

  <div class="flex items-end justify-center">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
  </div>

</form>



            <table class="w-full text-sm text-left">
              <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                <tr>
                  <th class="px-6 py-3">Sender Email</th>
                  <th class="px-6 py-3">Receiver Email</th>
                  <th class="px-6 py-3">Agent Email</th>
                  <th class="px-6 py-3">Amount</th>
                  <th class="px-6 py-3">Currency</th>
                  <th class="px-6 py-3">Status</th>
                  <th class="px-6 py-3">Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($walletToPerson as $transaction)
                  <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <td class="px-6 py-4">{{ $transaction->sender->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $transaction->receiver->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $transaction->agent->email??'N/A' }}</td>
                    <td class="px-6 py-4">{{ number_format($transaction->amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $transaction->currency }}</td>
                    <td class="px-6 py-4">
                      <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$transaction->status] ?? 'text-gray-600 bg-gray-100' }}">
                        {{ ucfirst($transaction->status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                      No wallet-to-person transactions found.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>
</div>
@endsection
