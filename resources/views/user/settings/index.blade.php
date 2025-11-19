
@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">
  <!-- Sidebar -->
  @include('components.user-sidebar')

  <!-- Main Content -->
  <div class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-10">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
      @include('components.user-notification-center')
    </header>

    @if(session('success'))
      <div class="max-w-xl mx-auto mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="max-w-xl mx-auto mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
        {{ session('error') }}
      </div>
    @endif

    <div class="max-w-xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="space-y-8">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Account</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage your payment preferences.</p>
        </div>

        <a href="{{ route('user.payment-methods.index') }}"
           class="w-full bg-transparent text-gray-900 dark:text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-between gap-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-4">
            <span class="material-symbols-outlined text-gray-500 dark:text-gray-400">credit_card</span>
            <span>Payment Methods</span>
          </div>
          <span class="material-symbols-outlined text-lg text-gray-400 dark:text-gray-500">chevron_right</span>
        </a>
      </div>
    </div>

    <!-- Agent Request Section -->
    @if(!$user->hasAnyRole(['Admin', 'Agent']))
    <div class="max-w-xl mx-auto mt-6 bg-white dark:bg-gray-800 p-8 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="space-y-6">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Become an Agent</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Request to become an agent and help process money transfers.</p>
        </div>

        @if($user->agent_request_status === 'pending')
          <div class="p-4 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700">
            <div class="flex items-center gap-2 mb-2">
              <span class="material-symbols-outlined">pending</span>
              <span class="font-semibold">Request Pending</span>
            </div>
            <p class="text-sm">Your request to become an agent is currently being reviewed by the admin.</p>
            <form method="POST" action="{{ route('user.settings.cancel-agent-request') }}" class="mt-3">
              @csrf
              <button type="submit" class="text-sm text-yellow-700 dark:text-yellow-300 hover:underline">
                Cancel Request
              </button>
            </form>
          </div>
        @elseif($user->agent_request_status === 'approved')
          <div class="p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-700">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined">check_circle</span>
              <span class="font-semibold">Request Approved</span>
            </div>
            <p class="text-sm mt-1">Your request has been approved! You are now an agent.</p>
          </div>
        @elseif($user->agent_request_status === 'rejected')
          <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-700 mb-4">
            <div class="flex items-center gap-2 mb-2">
              <span class="material-symbols-outlined">cancel</span>
              <span class="font-semibold">Request Rejected</span>
            </div>
            <p class="text-sm">Your request to become an agent was rejected. You can submit a new request below.</p>
          </div>
        @endif

        @if($user->agent_request_status !== 'pending' && $user->agent_request_status !== 'approved')
          {{-- Show form if no pending request and not already approved --}}
          <form method="POST" action="{{ route('user.settings.request-agent') }}" class="space-y-4">
            @csrf
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                City <span class="text-red-500">*</span>
              </label>
              <input type="text" 
                     name="city" 
                     value="{{ old('city', $user->city) }}"
                     required
                     class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                     placeholder="Enter your city">
              @error('city')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Phone Number <span class="text-red-500">*</span>
              </label>
              <div class="flex items-center gap-2">
                <span class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-700">+961</span>
                <input type="text" 
                       name="phone" 
                       value="{{ old('phone', $user->phone) }}"
                       required
                       pattern="[0-9]{8}"
                       maxlength="8"
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-r-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="12345678">
              </div>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter exactly 8 digits</p>
              @error('phone')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
              @enderror
            </div>

            <button type="submit" 
                    class="w-full bg-primary text-white font-semibold py-3 px-4 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
              {{ $user->agent_request_status === 'rejected' ? 'Submit New Request' : 'Request to Become an Agent' }}
            </button>
          </form>
        @endif
      </div>
    </div>
    @endif
</div>
</div>

@endsection
