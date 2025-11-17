
@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - Transferly</title>
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
@include('components.user-sidebar')
<!-- Main Content -->
<main class="flex-1">
<header class="flex h-20 items-center justify-end border-b border-[#CCCCCC] px-8 dark:border-white/20">
<div class="flex items-center gap-6">
<div class="relative">
  <button type="button" id="userNotifButton" class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-black/5 dark:hover:bg-white/10">
    <span class="material-symbols-outlined text-black dark:text-white text-2xl cursor-pointer">notifications</span>
    @if(($unreadNotifications ?? 0) > 0)
      <span id="userNotifDot" class="absolute top-2 right-2 inline-flex h-2 w-2 rounded-full bg-red-500"></span>
    @endif
  </button>

  <div id="userNotifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 border border-[#CCCCCC] dark:border-white/10 rounded-xl shadow-2xl z-50">
    <div class="px-4 py-3 border-b border-[#CCCCCC] dark:border-white/10 flex items-center justify-between">
      <div>
        <p class="text-sm font-semibold text-black dark:text-white">Notifications</p>
        <p id="userNotifUnreadText" class="text-xs text-black/60 dark:text-white/60">{{ $unreadNotifications ?? 0 }} unread</p>
      </div>
      <button id="userNotifClear" type="button" class="text-xs font-semibold text-black dark:text-white hover:underline disabled:opacity-40" {{ ($unreadNotifications ?? 0) === 0 ? 'disabled' : '' }}>
        Clear
      </button>
    </div>

    @if(isset($notifications) && $notifications->isNotEmpty())
      <ul class="max-h-80 overflow-y-auto divide-y divide-[#EEEEEE] dark:divide-white/5">
        @foreach($notifications as $notification)
          <li class="px-4 py-3 {{ !$notification->is_read ? 'bg-black/5 dark:bg-white/5' : '' }}">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-black dark:text-white">{{ $notification->title }}</p>
                @if($notification->message)
                  <p class="text-xs text-black/60 dark:text-white/70 mt-1">{{ $notification->message }}</p>
                @endif
              </div>
              <span class="text-[10px] text-black/50 dark:text-white/50 whitespace-nowrap">{{ $notification->created_at?->diffForHumans() }}</span>
            </div>
          </li>
        @endforeach
      </ul>
    @else
      <div class="p-4 text-sm text-black/60 dark:text-white/60">
        No notifications yet.
      </div>
    @endif
  </div>
</div>
</div>
</header>

<div class="p-8">
<div class="mx-auto max-w-4xl">

<!-- Welcome -->
<div class="flex flex-wrap items-center justify-between gap-3">
<p class="text-black dark:text-white text-4xl font-black leading-tight tracking-tighter">
    Welcome back, {{ $user->name }}
</p>
</div>

<!-- Balance Display -->
<div class="mt-8 flex flex-col gap-6 rounded-xl border border-[#CCCCCC] p-8 dark:border-white/20">
<div class="flex items-center justify-between">
  <h1 class="text-black/60 dark:text-white/60 tracking-normal text-base font-medium text-left">Total Balance</h1>
  <!-- Currency Selector -->
  <form method="GET" action="{{ route('user.dashboard') }}" class="flex items-center gap-2">
    <select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border border-[#CCCCCC] dark:border-white/20 dark:bg-gray-800 dark:text-white px-3 py-1.5 focus:ring-2 focus:ring-black dark:focus:ring-white">
      @foreach($currencies as $code => $name)
        <option value="{{ $code }}" {{ $selectedCurrency === $code ? 'selected' : '' }}>
          {{ $code }} - {{ $name }}
        </option>
      @endforeach
    </select>
  </form>
</div>
<p class="text-black dark:text-white text-5xl font-black tracking-tighter -mt-4">
  {{ \App\Services\CurrencyService::format($convertedBalance, $selectedCurrency) }}
</p>
@if($selectedCurrency !== 'USD')
  <p class="text-sm text-black/60 dark:text-white/60 mt-1">
    ≈ ${{ number_format($user->balance, 2) }} USD
  </p>
@endif

@if($walletFee > 0)
  <div class="mt-2 py-2 px-3 text-sm rounded-lg 
              bg-black/5 dark:bg-white/10 
              text-black/70 dark:text-white/70">
      Wallet to Wallet transfers include a 
      <span class="font-bold">{{ $walletFee }}%</span> service fee.
  </div>
@endif

<!-- Actions -->
<div class="flex flex-1 gap-4 flex-wrap justify-start mt-2">
<a href="{{ route('user.transfer') }}" class="flex min-w-[84px] items-center justify-center gap-2 rounded-full h-12 px-6 bg-black text-white text-base font-bold hover:opacity-80 dark:bg-white dark:text-black">
    <span class="material-symbols-outlined">north_east</span>
    <span>Send Money</span>
</a>
<a href="{{ route('user.transactions') }}" class="flex min-w-[84px] items-center justify-center gap-2 rounded-full h-12 px-6 border border-black text-black text-base font-bold hover:bg-black/5 dark:border-white dark:text-white dark:hover:bg-white/10">
    <span class="material-symbols-outlined">receipt_long</span>
    <span>View History</span>
</a>
</div>
</div>

<!-- Recent Transactions -->
<div class="mt-10">
<div class="flex items-center justify-between">
<h2 class="text-black dark:text-white text-[22px] font-bold leading-tight tracking-tight">Recent Transactions</h2>
<a href="{{ route('user.transactions') }}" class="text-black dark:text-white text-sm font-bold underline">View All</a>
</div>

<div class="mt-5 flow-root">
<div class="divide-y divide-[#CCCCCC] dark:divide-white/20">

@forelse($transactions as $txn)
@php
    $txnCurrency = $txn->currency ?? 'USD';
    $convertedAmount = \App\Services\CurrencyService::convert($txn->amount, $selectedCurrency, $txnCurrency);

    $receivedAmount = $convertedAmount;

    // Only adjust for Wallet-to-Wallet and receiver
    if($txn->service_type === 'wallet_to_wallet' && $txn->receiver_id == $user->id) {
        $feePercent = \App\Models\User::role('Admin')->first()->commission ?? 0;
        $receivedAmount = $convertedAmount * (1 - $feePercent/100);
    }
@endphp

<div class="flex items-center justify-between gap-4 py-4">
    <div class="flex items-center gap-4">
        <div class="flex size-10 items-center justify-center rounded-full {{ $txn->sender_id == $user->id ? 'bg-black/5 dark:bg-white/10' : 'bg-green-100 dark:bg-green-900/30' }}">
            <span class="material-symbols-outlined text-black dark:text-white">
                {{ $txn->sender_id == $user->id ? 'call_made' : 'call_received' }}
            </span>
        </div>
        <div>
            <p class="font-bold text-black dark:text-white">
                {{ $txn->sender_id == $user->id ? 'Sent to ' . $txn->receiver->name : 'Received from ' . $txn->sender->name }}
            </p>
            <p class="text-sm text-black/60 dark:text-white/60">{{ $txn->created_at->format('M d, Y') }}</p>
        </div>
    </div>
    <div class="text-right">
        <p class="font-bold text-black dark:text-white">
            {{ $txn->sender_id == $user->id ? '-' : '+' }}{{ \App\Services\CurrencyService::format($txn->sender_id == $user->id ? $convertedAmount : $receivedAmount, $selectedCurrency) }}
        </p>
        @if($txn->receiver_id == $user->id && $txn->service_type === 'wallet_to_wallet' && $convertedAmount != $receivedAmount)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                (Sent: {{ \App\Services\CurrencyService::format($convertedAmount, $selectedCurrency) }})
            </p>
        @endif
        <p class="text-sm {{ $txn->status == 'pending' ? 'text-yellow-600 dark:text-yellow-400' : 'text-black/60 dark:text-white/60' }}">
            {{ ucfirst($txn->status) }}
        </p>
    </div>
</div>
@empty
    <p class="text-black/60 dark:text-white/60 mt-4">No transactions found.</p>
@endforelse


</div>
</div>

<!-- Reviews & Ratings Section -->
<div class="mt-10">
  <div class="flex items-center justify-between mb-5">
    <h2 class="text-black dark:text-white text-[22px] font-bold leading-tight tracking-tight">Service Reviews</h2>
    @if($totalReviews > 0)
      <div class="flex items-center gap-2">
        <div class="flex items-center">
          @for($i = 1; $i <= 5; $i++)
            <span class="material-symbols-outlined text-xl {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-400' }}">
              star
            </span>
          @endfor
        </div>
        <span class="text-black dark:text-white text-sm font-semibold">
          {{ number_format($averageRating, 1) }} ({{ $totalReviews }} {{ $totalReviews === 1 ? 'review' : 'reviews' }})
        </span>
      </div>
    @endif
  </div>

  <!-- Add/Edit Review Form -->
  <div class="bg-white dark:bg-gray-900 border border-[#CCCCCC] dark:border-white/20 rounded-xl p-6 mb-6">
    <h3 class="text-lg font-bold text-black dark:text-white mb-4">
      {{ $userReview ? 'Update Your Review' : 'Write a Review' }}
    </h3>
    
    @if(session('success'))
      <div class="mb-4 p-3 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm">
        {{ session('success') }}
      </div>
    @endif

@if($userReview)
  @if(!$userReview->is_approved)
    <div class="mb-4 p-3 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 text-sm flex items-center gap-2">
      <span class="material-symbols-outlined text-base">hourglass_empty</span>
      <span>Your latest review is pending admin approval.</span>
    </div>
  @else
    <div class="mb-4 p-3 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm flex items-center gap-2">
      <span class="material-symbols-outlined text-base">verified</span>
      <span>Approved on {{ $userReview->approved_at?->format('M d, Y H:i') ?? $userReview->updated_at->format('M d, Y H:i') }}.</span>
    </div>
  @endif
@endif

    @if($errors->any())
      <div class="mb-4 p-3 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-sm">
        <ul class="list-disc pl-5">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('user.reviews.store') }}" method="POST">
      @csrf
      
      <!-- Rating Stars -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-black dark:text-white mb-2">Your Rating</label>
        <div class="flex items-center gap-2" id="rating-stars">
          @for($i = 1; $i <= 5; $i++)
            <button type="button" class="star-btn" data-rating="{{ $i }}">
              <span class="material-symbols-outlined text-3xl text-gray-300 dark:text-gray-600 hover:text-yellow-400 transition-colors">
                star
              </span>
            </button>
          @endfor
        </div>
        <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', $userReview ? $userReview->rating : 0) }}" required>
        @error('rating')
          <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Comment -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-black dark:text-white mb-2">Your Review (Optional)</label>
        <textarea 
          name="comment" 
          rows="4" 
          placeholder="Share your experience with our service..."
          class="w-full rounded-lg border border-[#CCCCCC] dark:border-white/20 dark:bg-gray-800 dark:text-white px-4 py-2 focus:ring-2 focus:ring-black dark:focus:ring-white"
        >{{ old('comment', $userReview ? $userReview->comment : '') }}</textarea>
        @error('comment')
          <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- Submit Button -->
      <div class="flex items-center gap-3">
        <button type="submit" class="flex items-center justify-center gap-2 rounded-full h-10 px-6 bg-black text-white text-sm font-bold hover:opacity-80 dark:bg-white dark:text-black">
          <span class="material-symbols-outlined text-sm">rate_review</span>
          <span>{{ $userReview ? 'Update Review' : 'Submit Review' }}</span>
        </button>
      </div>
    </form>
    
    @if($userReview)
      <form action="{{ route('user.reviews.destroy') }}" method="POST" class="mt-3">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline" onclick="return confirm('Are you sure you want to delete your review?')">
          Delete Review
        </button>
      </form>
    @endif
  </div>

  <!-- Recent Reviews -->
  @if($reviews->isNotEmpty())
    <div class="space-y-4">
      <h3 class="text-lg font-bold text-black dark:text-white">Recent Reviews</h3>
      @foreach($reviews as $review)
        <div class="bg-white dark:bg-gray-900 border border-[#CCCCCC] dark:border-white/20 rounded-xl p-4">
          <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-black dark:text-white">person</span>
              </div>
              <div>
                <p class="font-bold text-black dark:text-white">{{ $review->user->name }}</p>
              <p class="text-xs text-black/60 dark:text-white/60">{{ $review->approved_at?->format('M d, Y') ?? $review->created_at->format('M d, Y') }}</p>
              </div>
            </div>
            <div class="flex items-center">
              @for($i = 1; $i <= 5; $i++)
                <span class="material-symbols-outlined text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-400' }}">
                  star
                </span>
              @endfor
            </div>
          </div>
          @if($review->comment)
            <p class="text-sm text-black/80 dark:text-white/80 mt-2">{{ $review->comment }}</p>
          @endif
        </div>
      @endforeach
    </div>
  @else
    <div class="bg-white dark:bg-gray-900 border border-[#CCCCCC] dark:border-white/20 rounded-xl p-6 text-center">
      <p class="text-black/60 dark:text-white/60">No reviews yet. Be the first to review our service!</p>
    </div>
  @endif
</div>

</div>
</div>
</div>
</div>
</main>
</div>
</div>

<script>
  const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

  const userNotifButton = document.getElementById('userNotifButton');
  const userNotifDropdown = document.getElementById('userNotifDropdown');
  const userNotifClearBtn = document.getElementById('userNotifClear');
  const userNotifUnreadText = document.getElementById('userNotifUnreadText');
  const userNotifDot = document.getElementById('userNotifDot');
  const userNotifRoute = "{{ route('user.notifications.read') }}";

  if (userNotifButton && userNotifDropdown) {
    userNotifButton.addEventListener('click', (event) => {
      event.stopPropagation();
      userNotifDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
      if (
        !userNotifDropdown.contains(event.target) &&
        !userNotifButton.contains(event.target)
      ) {
        userNotifDropdown.classList.add('hidden');
      }
    });
  }

  if (userNotifClearBtn) {
    userNotifClearBtn.addEventListener('click', () => {
      if (!csrfToken || userNotifClearBtn.disabled) {
        return;
      }

      userNotifClearBtn.disabled = true;

      fetch(userNotifRoute, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
      })
        .then(() => {
          if (userNotifUnreadText) {
            userNotifUnreadText.textContent = '0 unread';
          }
          if (userNotifDot) {
            userNotifDot.remove();
          }
        })
        .catch(() => {
          userNotifClearBtn.disabled = false;
        });
    });
  }

  // Star rating interaction
  const starButtons = document.querySelectorAll('.star-btn');
  const ratingInput = document.getElementById('rating-input');
  const stars = document.querySelectorAll('.star-btn .material-symbols-outlined');

  // Initialize stars based on current rating
  function updateStars(rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.textContent = 'star';
        star.classList.remove('text-gray-300', 'dark:text-gray-600');
        star.classList.add('text-yellow-400');
      } else {
        star.textContent = 'star';
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300', 'dark:text-gray-600');
      }
    });
  }

  // Set initial rating if exists
  const initialRating = parseInt(ratingInput.value) || 0;
  if (initialRating > 0) {
    updateStars(initialRating);
  }

  // Add click handlers
  starButtons.forEach((button, index) => {
    button.addEventListener('click', function() {
      const rating = index + 1;
      ratingInput.value = rating;
      updateStars(rating);
    });

    button.addEventListener('mouseenter', function() {
      const rating = index + 1;
      updateStars(rating);
    });
  });

  // Reset on mouse leave (if no rating selected)
  document.getElementById('rating-stars').addEventListener('mouseleave', function() {
    const currentRating = parseInt(ratingInput.value) || 0;
    updateStars(currentRating);
  });
</script>
</body>
</html>
@endsection
