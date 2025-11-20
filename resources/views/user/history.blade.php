@extends('layouts.app', ['noNav' => true])
@section('content')
<div class="flex h-screen">
  <!-- Sidebar -->
@include('components.user-sidebar')

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
 <header class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-800">
      <a href="{{ route('user.refunds.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800 transition">
        <span class="material-symbols-outlined !text-base">assignment_returned</span>
        Refund Requests
      </a>
            @include('components.user-notification-center')
    </header>

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100">All Transactions</h1>

      @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
          {{ session('error') }}
        </div>
      @endif

      <div class="bg-white dark:bg-zinc-900/50 p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Transaction History</h2>
          <div class="flex items-center gap-4">

<form method="GET" action="{{ route('user.transactions') }}" id="filterForm" class="flex items-center gap-2">

  <select name="type" onchange="this.form.submit()" class="border rounded-lg p-2 text-sm">
    <option value="">All Types</option>
    <option value="sent" {{ request('type') == 'sent' ? 'selected' : '' }}>Sent</option>
    <option value="received" {{ request('type') == 'received' ? 'selected' : '' }}>Received</option>
  </select>

  <select name="status" onchange="this.form.submit()" class="border rounded-lg p-2 text-sm">
    <option value="">All Statuses</option>
    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
  </select>

  <input type="date" name="from_date" value="{{ request('from_date') }}" onchange="this.form.submit()" class="border rounded-lg p-2 text-sm"/>
  <input type="date" name="to_date" value="{{ request('to_date') }}" onchange="this.form.submit()" class="border rounded-lg p-2 text-sm"/>

<select name="sort" onchange="this.form.submit()" class="border rounded-lg p-2 text-sm">
  <option value="">Sort By</option>
  <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
  <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
  <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High → Low)</option>
  <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low → High)</option>
</select>

  <select name="currency" onchange="this.form.submit()" class="border rounded-lg p-2 text-sm">
    @foreach($currencies as $code => $name)
      <option value="{{ $code }}" {{ $selectedCurrency === $code ? 'selected' : '' }}>
        {{ $code }}
      </option>
    @endforeach
  </select>


  <a href="{{ route('user.transactions') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
    Reset
  </a>
</form>


<div class="relative inline-block">
  <button id="exportBtn" class="flex items-center gap-2 ...">
    <span class="material-symbols-outlined !text-base">download</span> Export
  </button>
  <div id="exportMenu" class="hidden absolute mt-2 bg-white border rounded shadow">
<a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="block px-4 py-2 text-sm">PDF</a>
<a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="block px-4 py-2 text-sm">CSV</a>

  </div>
</div>

          </div>
        </div>

        <div class="space-y-2">
          @forelse($transactions as $txn)
            @php
              $isOutgoing = $txn->sender_id == Auth::id();
              $otherParty = $isOutgoing ? $txn->receiver : $txn->sender;
              $canAddBeneficiary = $otherParty !== null && !$otherParty->hasRole('Agent');
              $otherPartyName = $otherParty->name ?? '';
              $otherPartyPhone = $otherParty->phone ?? '';
              $alreadyBeneficiary = $canAddBeneficiary && (
                in_array($otherPartyName, $beneficiaryNames) || 
                ($otherPartyPhone && in_array($otherPartyPhone, $beneficiaryPhones))
              );
            @endphp
            <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-800">
              <!-- Icon -->
              <div class="w-10 h-10 rounded-full 
                @if($isOutgoing)
                  bg-gray-100 dark:bg-gray-800
                @else
                  bg-green-100 dark:bg-green-900/50
                @endif
                flex items-center justify-center mr-4">
                <span class="material-symbols-outlined
                  @if($isOutgoing)
                    text-gray-600 dark:text-gray-400
                  @else
                    text-green-600 dark:text-green-400
                  @endif">
                  {{ $isOutgoing ? 'north_east' : 'south_west' }}
                </span>
              </div>

              <!-- Details -->
              <div class="flex-1">
                @if($isOutgoing)
                  <p class="font-semibold text-gray-900 dark:text-gray-100">
                    Sent to 
                    {{-- Check for recipient_name if receiver is null --}}
                    {{ $txn->receiver->name ?? $txn->recipient_name ?? 'Unknown' }} 
                  </p>
                @else
                  <p class="font-semibold text-gray-900 dark:text-gray-100">
                    Received from {{ $txn->sender->name ?? 'Unknown' }}
                  </p>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $txn->created_at->format('M d, Y H:i') }}</p>
              </div>

              <!-- Amount & Status & Action -->
              <div class="flex items-center gap-4">
                <div class="text-right">
                  @php
                    $txnCurrency = $txn->currency ?? 'USD';
                    $displayAmount = \App\Services\CurrencyService::convert($txn->amount, $selectedCurrency, $txnCurrency);
                  @endphp
                  <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $isOutgoing ? '-' : '+' }}{{ \App\Services\CurrencyService::format($displayAmount, $selectedCurrency) }}
                  </p>
                  @if($selectedCurrency !== $txnCurrency)
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ \App\Services\CurrencyService::format($txn->amount, $txnCurrency) }}
                    </p>
                  @endif
                  @php
                    $statusColors = [
                      'completed' => 'text-green-600 dark:text-green-500',
                      'pending' => 'text-yellow-600 dark:text-yellow-400',
                      'pending_agent' => 'text-yellow-600 dark:text-yellow-400',
                      'in_progress' => 'text-yellow-600 dark:text-yellow-400',
                      'suspicious' => 'text-orange-600 dark:text-orange-400',
                      'disputed' => 'text-purple-600 dark:text-purple-400',
                      'refunded' => 'text-blue-600 dark:text-blue-400',
                      'failed' => 'text-red-500 dark:text-red-400',
                    ];
                  @endphp
                  @php
                    $statusLabel = $txn->status === 'disputed'
                      ? 'Refund pending'
                      : ucfirst(str_replace('_', ' ', $txn->status));
                  @endphp
                  <p class="text-sm {{ $statusColors[$txn->status] ?? 'text-gray-500 dark:text-gray-400' }}">
                    {{ $statusLabel }}
                  </p>
                </div>
                
                @if($canAddBeneficiary && !$alreadyBeneficiary)
                  <form action="{{ route('user.beneficiary.addFromTransaction', $txn->id) }}" method="POST" class="ml-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 border border-blue-600 dark:border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                      <span class="material-symbols-outlined !text-base">person_add</span>
                      Add to Beneficiary
                    </button>
                  </form>
                @elseif($canAddBeneficiary && $alreadyBeneficiary)
                  <span class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span class="material-symbols-outlined !text-base">check_circle</span>
                    In Beneficiaries
                  </span>
                @endif
              </div>
            </div>
          @empty
            <p class="text-center text-gray-500 dark:text-gray-400 py-6">No transactions yet.</p>
          @endforelse
        </div>
        <div class="mt-6">
    {{ $transactions->links('pagination::tailwind') }}
</div>

      </div>
    </div>
  </main>
</div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) {
      console.warn('Filter form not found: ensure the form has id="filterForm"');
      return;
    }
    const inputs = filterForm.querySelectorAll('select, input[type="date"]');

    inputs.forEach(input => {
      input.addEventListener('change', () => {
        filterForm.submit();
      });
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const exportBtn = document.getElementById('exportBtn');
    const exportMenu = document.getElementById('exportMenu');

    if (exportBtn && exportMenu) {
        exportBtn.addEventListener('click', () => {
            exportMenu.classList.toggle('hidden');
        });
    }
  });
</script>
