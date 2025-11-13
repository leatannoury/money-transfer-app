@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Manage Reviews - Admin</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#2563eb",
            "background-light": "#F9FAFB",
            "background-dark": "#111827",
            "card-light": "#FFFFFF",
            "card-dark": "#1F2937",
            "border-light": "#E5E7EB",
            "border-dark": "#374151",
          },
          fontFamily: {
            "display": ["Inter", "sans-serif"]
          },
        },
      },
    }
  </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
  <div class="flex min-h-screen">
    @include('components.admin-sidebar')
    <main class="flex-1 flex flex-col">
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Manage Reviews</h2>
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
    </main>
  </div>
</body>
</html>
@endsection

