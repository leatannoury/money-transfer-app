@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="p-8 max-w-2xl mx-auto">
  <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white text-center">Beneficiary Details</h1>

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
@endsection
