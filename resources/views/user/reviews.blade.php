@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">
  <!-- Sidebar -->
  @include('components.user-sidebar')
  
  <!-- Main Content -->
  <div class="flex-1 overflow-y-auto">
    <header class="flex h-20 items-center justify-end border-b border-[#CCCCCC] px-8 dark:border-white/20">
      @include('components.user-notification-center')
    </header>

    <div class="p-8">
      <div class="mx-auto max-w-4xl">
        <!-- Page Title -->
        <div class="mb-8">
          <h1 class="text-black dark:text-white text-4xl font-black leading-tight tracking-tighter">
            Reviews & Ratings
          </h1>
          <p class="text-black/60 dark:text-white/60 mt-2">Share your experience and see what others are saying</p>
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
                  <span>Your review is waiting for admin approval. It will appear publicly once approved.</span>
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

          <!-- All Reviews -->
          @if($reviews->isNotEmpty())
            <div class="space-y-4">
              <h3 class="text-lg font-bold text-black dark:text-white">All Reviews</h3>
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

<script>
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

@endsection

