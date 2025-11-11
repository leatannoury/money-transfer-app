@extends('layouts.app', ['noNav' => true])

@section('content')
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
        <a href="{{ route('user.beneficiary.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
          <span class="material-symbols-outlined">people</span>
          <span>Beneficiaries</span>
        </a>
        <a href="#" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">settings</span>
          <span>Settings</span>
        </a>
      </nav>
    </div>

    <div class="flex items-center gap-3">
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

    <div class="p-8 max-w-3xl">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Beneficiary Details</h1>
      <div class="bg-white dark:bg-zinc-900/50 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
        <p><strong>Full Name:</strong> {{ $beneficiary->full_name }}</p>
        <p><strong>Payout Method:</strong> {{ $beneficiary->payout_method }}</p>
        <p><strong>Account Number:</strong> {{ $beneficiary->account_number }}</p>
        <p><strong>Phone Number:</strong> {{ $beneficiary->phone_number ?? '-' }}</p>
        <p><strong>Address:</strong> {{ $beneficiary->address ?? '-' }}</p>
        <div class="flex gap-2 mt-4">
          <a href="{{ route('user.beneficiary.edit', $beneficiary->id) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:opacity-90">Edit</a>
          <form action="{{ route('user.beneficiary.destroy', $beneficiary->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Are you sure?');" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:opacity-90">Delete</button>
          </form>
          <a href="{{ route('user.beneficiary.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:opacity-90">Back to List</a>
        </div>
      </div>
    </div>
  </main>
</div>
@endsection
