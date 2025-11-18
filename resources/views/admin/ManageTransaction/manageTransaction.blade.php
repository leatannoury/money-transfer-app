@extends('layouts.app', ['noNav' => true])

@section('content')


<div class="relative flex h-auto min-h-screen w-full flex-col">
  <div class="flex min-h-screen">
    @include('components.admin-sidebar')

    <div class="flex-1 flex flex-col">
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
            <div class="p-4 flex justify-center">
        {{ $walletToWallet->links('pagination::tailwind') }}
    </div>
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
      <option value="In_progress" {{ request('person_status') == 'In_progress' ? 'selected' : '' }}>In_progress</option>
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
             <div class="p-4 flex justify-center">
      {{ $walletToPerson->links('pagination::tailwind') }}
    </div>
          </div>
        </div>




        {{-- CASH IN TRANSACTIONS --}}
<div>
    <h3 class="text-lg font-semibold mb-4">Cash In Transactions</h3>
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">

        <form method="GET" action="{{ url()->current() }}" 
              class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-4 max-w-6xl mx-auto justify-between items-end">

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">Email</label>
                <input type="text" name="cash_in_email" value="{{ request('cash_in_email') }}"
                       placeholder="example@email.com"
                       class="mt-1 block w-full rounded-md border-gray-300 text-center">
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">Status</label>
                <select name="cash_in_status" class="mt-1 block w-full rounded-md border-gray-300 text-center">
                    <option value="">All</option>
                    <option value="completed" {{ request('cash_in_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('cash_in_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">From Date</label>
                <input type="date" name="cash_in_from" value="{{ request('cash_in_from') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 text-center">
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">To Date</label>
                <input type="date" name="cash_in_to" value="{{ request('cash_in_to') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 text-center">
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">Sort</label>
                <select name="cash_in_sort" class="mt-1 block w-full rounded-md border-gray-300 text-center">
                    <option value="">Newest</option>
                    <option value="oldest" {{ request('cash_in_sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="amount_desc" {{ request('cash_in_sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High → Low)</option>
                    <option value="amount_asc" {{ request('cash_in_sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low → High)</option>
                </select>
            </div>

            <div class="flex items-end justify-center">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Filter
                </button>
            </div>

        </form>

        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-3">Agent Email</th>
                    <th class="px-6 py-3">User Email</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Currency</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($cashIn as $t)
                <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <td class="px-6 py-4">{{ $t->sender->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $t->receiver->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ number_format($t->amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $t->currency }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $t->status === 'completed' ? 'text-green-600 bg-green-100' :
                               ($t->status === 'failed' ? 'text-red-600 bg-red-100' : 'text-gray-600 bg-gray-100') }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No Cash In transactions found.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="p-4 flex justify-center">
            {{ $cashIn->links('pagination::tailwind') }}
        </div>

    </div>
</div>



{{-- CASH OUT TRANSACTIONS --}}
<div>
    <h3 class="text-lg font-semibold mb-4">Cash Out Transactions</h3>
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">

        <form method="GET" action="{{ url()->current() }}" 
              class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-4 max-w-6xl mx-auto">

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">Email</label>
                <input type="text" name="cash_out_email" value="{{ request('cash_out_email') }}"
                       placeholder="example@email.com"
                       class="mt-1 block w-full rounded-md border-gray-300 text-center">
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">Status</label>
                <select name="cash_out_status" class="mt-1 block w-full rounded-md border-gray-300 text-center">
                    <option value="">All</option>
                    <option value="completed" {{ request('cash_out_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('cash_out_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">From Date</label>
                <input type="date" name="cash_out_from" value="{{ request('cash_out_from') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 text-center">
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">To Date</label>
                <input type="date" name="cash_out_to" value="{{ request('cash_out_to') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 text-center">
            </div>

            <div class="flex flex-col items-center">
                <label class="text-sm font-medium text-center">Sort</label>
                <select name="cash_out_sort" class="mt-1 block w-full rounded-md border-gray-300 text-center">
                    <option value="">Newest</option>
                    <option value="oldest" {{ request('cash_out_sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="amount_desc" {{ request('cash_out_sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High → Low)</option>
                    <option value="amount_asc" {{ request('cash_out_sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low → High)</option>
                </select>
            </div>

            <div class="flex items-end justify-center">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Filter
                </button>
            </div>

        </form>

        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-3">User Email</th>
                    <th class="px-6 py-3">Agent Email</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Currency</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($cashOut as $t)
                <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <td class="px-6 py-4">{{ $t->sender->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $t->receiver->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ number_format($t->amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $t->currency }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $t->status === 'completed' ? 'text-green-600 bg-green-100' :
                               ($t->status === 'failed' ? 'text-red-600 bg-red-100' : 'text-gray-600 bg-gray-100') }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No Cash Out transactions found.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="p-4 flex justify-center">
            {{ $cashOut->links('pagination::tailwind') }}
        </div>

    </div>
</div>

      </div>
    </div>
  </div>
</div>
@endsection
