@extends('layouts.app')

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
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100">Add New Beneficiary</h1>

      @if (session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
          {{ session('error') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="bg-white dark:bg-zinc-900/50 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <form action="{{ route('user.beneficiary.store') }}" method="POST">
          @csrf
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Full Name</label>
              <input type="text" name="full_name" value="{{ old('full_name') }}" required
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Email</label>
              <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Payout Method</label>
              <input type="text" name="payout_method" value="{{ old('payout_method') }}" required
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Number</label>
              <input type="text" name="account_number" value="{{ old('account_number') }}"
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Phone Number</label>
              <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Address</label>
              <input type="text" name="address" value="{{ old('address') }}"
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <button type="submit"
              class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg">
              Add Beneficiary
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
@endsection
