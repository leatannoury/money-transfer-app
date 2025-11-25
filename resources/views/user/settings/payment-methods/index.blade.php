@extends('layouts.app', ['noNav' => true])

@section('content')


  <style>
    .material-icons-outlined { font-size: 24px; line-height: 1; }
    input[type=text]:focus, input[type=password]:focus { --tw-ring-color: #000000; }
    .dark input[type=text]:focus, .dark input[type=password]:focus { --tw-ring-color: #ffffff; }
  </style>


<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('components.user-sidebar')

    <div class="flex-1 p-8 overflow-y-auto">

        {{-- Header --}}
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Payment Methods</h1>
            @include('components.user-notification-center')
        </header>

        <div class="max-w-4xl mx-auto space-y-6">

            {{-- Success message --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- CREDIT CARDS SECTION --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 cursor-pointer flex justify-between items-center section-header" data-target="cards-content">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Credit Cards</h2>
                    <span class="material-icons-outlined text-gray-500 dark:text-gray-400 transition-transform">expand_more</span>
                </div>
                <div id="cards-content" class="hidden px-6 pb-6 space-y-4">
                    <a href="{{ route('user.payment-methods.create', ['type' => 'card']) }}"
                       class="w-full bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 font-semibold py-3 px-4 rounded-lg flex items-center justify-center gap-2 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark">
                      
                        <span>Add New Credit Card</span>
                    </a>

{{-- Credit Cards List --}}
@forelse($methods->where('type', 'credit_card') as $method)
    <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-8 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center">
                <span class="material-icons-outlined text-gray-500 dark:text-gray-400">credit_card</span>
            </div>
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $method->nickname ?? $method->provider . ' ending in ' . $method->last4 }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $method->provider }} <br> Expires {{ $method->expiry }}
                </p>
            </div>
        </div>

<div class="flex items-center gap-3">

    {{-- Primary Status --}}
<form action="{{ route('user.payment-methods.primary', $method->id) }}" method="POST" class="inline">
    @csrf
    @method('PUT')
    <button type="submit"
        class="flex items-center gap-1 transition-colors
               {{ $method->is_primary ? 'text-yellow-500 hover:text-gray-500' : 'text-gray-500 hover:text-yellow-500' }}">
        <span class="material-icons-outlined text-base">
            {{ $method->is_primary ? 'star' : 'star_outline' }}
        </span>
      
    </button>
</form>


    {{-- Edit --}}
    <a href="{{ route('user.payment-methods.edit', $method->id) }}" 
       class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
       <span class="material-icons-outlined text-base">edit</span> Edit
    </a>

    {{-- Delete --}}
    <button type="button"
            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-600 flex items-center gap-1"
            onclick="openDeleteModal({{ $method->id }})">
        <span class="material-icons-outlined text-base">delete</span> Delete
    </button>

</div>



    </div>
@empty
    <p class="text-gray-600 dark:text-gray-400">No saved credit cards.</p>
@endforelse


                </div>
            </div>

{{-- BANK ACCOUNTS SECTION --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 cursor-pointer flex justify-between items-center section-header" data-target="banks-content">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bank Accounts</h2>
        <span class="material-icons-outlined text-gray-500 dark:text-gray-400 transition-transform">expand_more</span>
    </div>
    <div id="banks-content" class="hidden px-6 pb-6 space-y-4">
        <a href="{{ route('user.payment-methods.create', ['type' => 'bank']) }}"
           class="w-full bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 font-semibold py-3 px-4 rounded-lg flex items-center justify-center gap-2 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark">
            <span>Add New Bank Account</span>
        </a>

 {{-- Bank Accounts List --}}
@forelse($methods->where('type', 'bank_account') as $method)
    <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-8 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center">
                <span class="material-icons-outlined text-gray-500 dark:text-gray-400">account_balance</span>
            </div>
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $method->nickname ?? 'Bank Account ending in ' . $method->last4 }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Account Holder: {{ $method->cardholder_name }} <br> Bank: {{ $method->provider }}
                </p>
            </div>
        </div>
        
<div class="flex items-center gap-3">

    {{-- Primary Status --}}
<form action="{{ route('user.payment-methods.primary', $method->id) }}" method="POST" class="inline">
    @csrf
    @method('PUT')
    <button type="submit"
        class="flex items-center gap-1 transition-colors
               {{ $method->is_primary ? 'text-yellow-500 hover:text-gray-500' : 'text-gray-500 hover:text-yellow-500' }}">
        <span class="material-icons-outlined text-base">
            {{ $method->is_primary ? 'star' : 'star_outline' }}
        </span>
       
    </button>
</form>


    {{-- Edit --}}
    <a href="{{ route('user.payment-methods.edit', $method->id) }}" 
       class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
       <span class="material-icons-outlined text-base">edit</span> Edit
    </a>

    {{-- Delete --}}
    <button type="button"
            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-600 flex items-center gap-1"
            onclick="openDeleteModal({{ $method->id }})">
        <span class="material-icons-outlined text-base">delete</span> Delete
    </button>

</div>


    </div>
@empty
    <p class="text-gray-600 dark:text-gray-400">No linked bank accounts yet.</p>
@endforelse

    </div>
</div>



        </div>
</div>
</div>


{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-sm">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delete Card</h3>
    <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete this credit card?</p>
    <div class="flex justify-end gap-3">
      <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        Cancel
      </button>
      <form id="deleteForm" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">
          Delete
        </button>
      </form>
    </div>
  </div>
</div>


<script>
  let deleteModal = document.getElementById('deleteModal');
  let deleteForm = document.getElementById('deleteForm');

  function openDeleteModal(id) {
    deleteForm.action = `/user/payment-methods/${id}`;
    deleteModal.classList.remove('hidden');
  }

  function closeDeleteModal() {
    deleteModal.classList.add('hidden');
  }
</script>


<script>
  // Collapsible section toggle with persistence
  document.querySelectorAll('.section-header').forEach(header => {
    const targetId = header.dataset.target;
    const content = document.getElementById(targetId);
    const icon = header.querySelector('.material-icons-outlined');

    // Restore previous state from localStorage
    const isOpen = localStorage.getItem(targetId) === 'open';
    if (isOpen) {
      content.classList.remove('hidden');
    }

    header.addEventListener('click', () => {
      const isHidden = content.classList.toggle('hidden');
      localStorage.setItem(targetId, isHidden ? 'closed' : 'open');
    });
  });
</script>

@endsection
