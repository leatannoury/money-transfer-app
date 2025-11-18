@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex h-screen">
  <!-- Sidebar -->
  @include('components.user-sidebar')
  <!-- Main Content -->
  <main class="flex-1">
    <header class="flex h-20 items-center justify-end border-b border-[#CCCCCC] px-8 dark:border-white/20">
      @include('components.user-notification-center')
    </header>

    <div class="p-8 max-w-3xl">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Edit Beneficiary</h1>
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
        <form action="{{ route('user.beneficiary.update', $beneficiary->id) }}" method="POST">
          @csrf
          @method('PATCH')
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Full Name</label>
              <input type="text" name="full_name" value="{{ old('full_name', $beneficiary->full_name) }}" required
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Payout Method</label>
              <input type="text" name="payout_method" value="{{ old('payout_method', $beneficiary->payout_method) }}" required
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Number</label>
              <input type="text" name="account_number" value="{{ old('account_number', $beneficiary->account_number) }}"
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Phone Number</label>
              <input type="text" name="phone_number" value="{{ old('phone_number', $beneficiary->phone_number) }}"
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Address</label>
              <input type="text" name="address" value="{{ old('address', $beneficiary->address) }}"
                class="w-full pl-3 pr-4 py-2.5 border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white">
            </div>
            <button type="submit"
              class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg">
              Update Beneficiary
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
@endsection
