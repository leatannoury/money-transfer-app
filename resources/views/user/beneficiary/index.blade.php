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

    <div class="p-8 max-w-5xl">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Beneficiaries</h1>
        <a href="{{ route('user.beneficiary.create') }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
           Add New
        </a>
      </div>

      @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ session('success') }}
        </div>
      @endif

      <div class="bg-white dark:bg-zinc-900/50 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-300 dark:border-gray-700">
              <th class="p-3">Full Name</th>
              <th class="p-3">Payout Method</th>
              <th class="p-3">Account Number</th>
              <th class="p-3">Phone Number</th>
              <th class="p-3">Address</th>
              <th class="p-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($beneficiaries as $b)
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <td class="p-3">{{ $b->full_name }}</td>
              <td class="p-3">{{ $b->payout_method }}</td>
              <td class="p-3">{{ $b->account_number }}</td>
              <td class="p-3">{{ $b->phone_number ?? '-' }}</td>
              <td class="p-3">{{ $b->address ?? '-' }}</td>
              <td class="p-3 text-center flex gap-2 justify-center">
                <a href="{{ route('user.beneficiary.show', $b->id) }}" class="text-blue-600 hover:underline">View</a>
                <a href="{{ route('user.beneficiary.edit', $b->id) }}" class="text-yellow-600 hover:underline">Edit</a>
                <form action="{{ route('user.beneficiary.destroy', $b->id) }}" method="POST" class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" onclick="return confirm('Are you sure?');" class="text-red-600 hover:underline">Delete</button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="p-3 text-center text-gray-500">No beneficiaries found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
@endsection
