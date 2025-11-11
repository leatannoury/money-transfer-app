@extends('layouts.app', ['noNav' => true])
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
        <a href="{{ route('user.beneficiary.index') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">people</span>
          <span>Beneficiaries</span>
        </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
<span class="material-symbols-outlined">settings</span>
<p >Settings</p>
</a>
      </nav>
    </div>
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
              $isCompleted = $txn->status == 'completed';
              $canAddBeneficiary = $isOutgoing && $isCompleted && $txn->receiver;
              $receiverName = $txn->receiver->name ?? '';
              $receiverPhone = $txn->receiver->phone ?? '';
              $alreadyBeneficiary = $canAddBeneficiary && (
                in_array($receiverName, $beneficiaryNames) || 
                ($receiverPhone && in_array($receiverPhone, $beneficiaryPhones))
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
                  <p class="font-semibold text-gray-900 dark:text-gray-100">Sent to {{ $txn->receiver->name ?? 'Unknown' }}</p>
                @else
                  <p class="font-semibold text-gray-900 dark:text-gray-100">Received from {{ $txn->sender->name ?? 'Unknown' }}</p>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $txn->created_at->format('M d, Y H:i') }}</p>
              </div>

              <!-- Amount & Status & Action -->
              <div class="flex items-center gap-4">
                <div class="text-right">
                  <p class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $isOutgoing ? '-' : '+' }}${{ number_format($txn->amount, 2) }}
                  </p>
                  <p class="text-sm 
                    @if($txn->status == 'completed') text-green-600 dark:text-green-500
                    @elseif($txn->status == 'pending') text-yellow-500
                    @else text-red-500
                    @endif">
                    {{ ucfirst($txn->status) }}
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
</body>
</html>
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
