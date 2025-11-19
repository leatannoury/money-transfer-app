@extends('layouts.app', ['noNav' => true])

@section('content')
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
 
  <style>
:root {
    --bg-card-light: #ffffff;
    --bg-card-dark: #1f2937;
    --text-light: #f9fafb;
    --text-dark: #111827;
}
.bg-card-light { background-color: var(--bg-card-light); }
.bg-card-dark { background-color: var(--bg-card-dark); }
.text-light { color: var(--text-light); }
.text-dark { color: var(--text-dark); }
</style>

  <div class="flex min-h-screen">
    @include('components.admin-sidebar')
    <div class="flex-1 overflow-y-auto">
     <header class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
      <h2 class="text-text-light dark:text-text-dark text-xl font-bold">Manage Review</h2>
      @include('components.admin-notification-center')
    </header>
      <div class="flex-1 p-8 overflow-y-auto space-y-8">

        @if(session('success'))
          <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">
            {{ session('success') }}
          </div>
        @endif

        @if(session('info'))
          <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 text-sm">
            {{ session('info') }}
          </div>
        @endif

        @if($pendingReviews->isEmpty() && $approvedReviews->isEmpty())
          <div class="rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 text-center text-gray-500 dark:text-gray-400">
            No reviews have been submitted yet.
          </div>
        @else
          <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
            <div class="border-b border-border-light dark:border-border-dark px-6 py-4 flex items-center justify-between">
              <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Pending Reviews</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ $pendingReviews->count() }} awaiting approval</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
                <thead class="bg-background-light dark:bg-background-dark text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                  <tr>
                    <th class="px-6 py-3 text-left">User</th>
                    <th class="px-6 py-3 text-left">Rating</th>
                    <th class="px-6 py-3 text-left">Comment</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Submitted</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark text-sm">
                  @forelse($pendingReviews as $review)
                    <tr class="bg-white dark:bg-card-dark">
                      <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $review->user->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $review->user->email }}</div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="inline-flex items-center gap-1 rounded-full bg-yellow-50 text-yellow-700 px-3 py-1 text-xs font-semibold dark:bg-yellow-900/30 dark:text-yellow-300">
                          <span class="material-symbols-outlined text-base">star</span>
                          {{ $review->rating }}/5
                        </div>
                      </td>
                      <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {{ $review->comment ?: '—' }}
                      </td>
                      <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                        {{ $review->created_at?->format('M d, Y H:i') }}
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                          <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-green-600 hover:bg-green-700 text-white px-3 py-1 text-xs font-semibold transition">
                              <span class="material-symbols-outlined text-sm">check</span>
                              Approve
                            </button>
                          </form>
                          <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Reject and remove this review?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 text-xs font-semibold transition">
                              <span class="material-symbols-outlined text-sm">close</span>
                              Reject
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No pending reviews.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </section>

          <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
            <div class="border-b border-border-light dark:border-border-dark px-6 py-4 flex items-center justify-between">
              <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Approved Reviews</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ $approvedReviews->count() }} published</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
                <thead class="bg-background-light dark:bg-background-dark text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                  <tr>
                    <th class="px-6 py-3 text-left">User</th>
                    <th class="px-6 py-3 text-left">Rating</th>
                    <th class="px-6 py-3 text-left">Comment</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Approved</th>
                    <th class="px-6 py-3 text-left">Approved By</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark text-sm">
                  @forelse($approvedReviews as $review)
                    <tr class="bg-white dark:bg-card-dark">
                      <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $review->user->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $review->user->email }}</div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="inline-flex items-center gap-1 rounded-full bg-green-50 text-green-700 px-3 py-1 text-xs font-semibold dark:bg-green-900/30 dark:text-green-300">
                          <span class="material-symbols-outlined text-base">star</span>
                          {{ $review->rating }}/5
                        </div>
                      </td>
                      <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {{ $review->comment ?: '—' }}
                      </td>
                      <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                        {{ $review->approved_at?->format('M d, Y H:i') ?? '—' }}
                      </td>
                      <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                        {{ $review->approver?->name ?? 'System' }}
                      </td>
                      <td class="px-6 py-4">
                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Remove this review? It will no longer appear to users.');" class="flex justify-end">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 text-xs font-semibold transition">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            Remove
                          </button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No approved reviews yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </section>
        @endif
      </div>
</div>
  </div>

@endsection

