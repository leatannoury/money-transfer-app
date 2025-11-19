@extends('layouts.app', ['noNav' => true])

@section('content')


  <style>
    .material-icons-outlined { font-size: 24px; line-height: 1; }
    input[type=text]:focus, input[type=password]:focus { --tw-ring-color: #000000; }
    .dark input[type=text]:focus, .dark input[type=password]:focus { --tw-ring-color: #ffffff; }
  </style>


<div class="flex min-h-screen">
  <!-- Sidebar -->
@include('components.user-sidebar')

  <!-- Main Content -->
  <div class="flex-1">
    <header class="flex h-20 items-center justify-end border-b border-[#CCCCCC] px-8 dark:border-white/20">
      @include('components.user-notification-center')
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
</div>
</div>
@endsection
