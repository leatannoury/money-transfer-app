@extends('layouts.app', ['noNav' => true])

@section('content')
<html class="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Requests</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": { DEFAULT: "#3B82F6" },
            "background-light": "#F9FAFB",
            "background-dark": "#111827",
            "card-light": "#FFFFFF",
            "card-dark": "#1F2937",
            "text-light": "#1F2937",
            "text-dark": "#F9FAFB",
            "border-light": "#E5E7EB",
            "border-dark": "#374151",
            "success": "#10B981",
            "warning": "#F59E0B",
            "error": "#EF4444",
          },
          fontFamily: { display: ["Inter", "sans-serif"] },
          borderRadius: { "xl": "1rem" }
        }
      }
    }
  </script>

  <style>
    .material-symbols-outlined {
      font-variation-settings:
      'FILL' 0,
      'wght' 400,
      'GRAD' 0,
      'opsz' 24
    }
  </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    @include('components.admin-sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-xl font-bold">Agent Requests</h2>
      </header>

      <div class="flex-1 p-8 overflow-y-auto">
        @if(session('success'))
          <div class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
            {{ session('error') }}
          </div>
        @endif

        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h3 class="text-lg font-semibold">Pending Agent Requests</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total: {{ $totalRequests }} request(s)</p>
          </div>
          <a href="{{ route('admin.agents') }}">
            <button class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center gap-1">
              <span class="material-symbols-outlined text-sm">arrow_back</span>
              <span>Back to Agents</span>
            </button>
          </a>
        </div>

        <!-- Search Filters -->
        <div class="@container mb-6">
          <div class="overflow-hidden rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Email</label>
                <input type="text" name="email" value="{{ request('email') }}" placeholder="example@email.com"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Phone</label>
                <input type="text" name="phone" value="{{ request('phone') }}" placeholder="12345678"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">City</label>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="City name"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              </div>

              <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-blue-600 transition">
                  Filter
                </button>
                <a href="{{ url()->current() }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-md hover:bg-gray-400 dark:hover:bg-gray-700 transition">
                  Reset
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- Requests Table -->
        <div class="@container">
          <div class="overflow-hidden rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
            <table class="w-full">
              <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">City</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Date</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($requests as $request)
                <tr class="border-t border-border-light dark:border-border-dark hover:bg-gray-50 dark:hover:bg-gray-800">
                  <td class="px-6 py-4">{{ $request->name }}</td>
                  <td class="px-6 py-4">{{ $request->email }}</td>
                  <td class="px-6 py-4">{{ $request->phone ? '+961 '.$request->phone : 'N/A' }}</td>
                  <td class="px-6 py-4">{{ $request->city ?? 'N/A' }}</td>
                  <td class="px-6 py-4">{{ $request->created_at->format('M d, Y') }}</td>
                  <td class="px-6 py-4">
                    <div class="flex gap-2">
                      <!-- Approve Button with Modal -->
                      <button onclick="openApproveModal({{ $request->id }}, '{{ $request->name }}')" 
                              class="px-3 py-1 bg-success text-white rounded-lg text-xs font-medium hover:bg-green-600 transition">
                        Approve
                      </button>
                      
                      <!-- Reject Button -->
                      <form action="{{ route('admin.agents.requests.reject', $request->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to reject {{ $request->name }}\'s request?');" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-error text-white rounded-lg text-xs font-medium hover:bg-red-600 transition">
                          Reject
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                      <span class="material-symbols-outlined text-4xl">inbox</span>
                      <p>No pending agent requests</p>
                    </div>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
            <div class="p-4 flex justify-center">
              {{ $requests->links('pagination::tailwind') }}
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Approve Agent Request</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
      Approve <span id="userName" class="font-semibold"></span>'s request to become an agent?
    </p>
    <form id="approveForm" method="POST" action="">
      @csrf
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Commission Rate (%) <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               name="commission" 
               min="0" 
               max="100" 
               step="0.01"
               required
               value="5"
               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
               placeholder="Enter commission rate">
        @error('commission')
          <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror
      </div>
      <div class="flex gap-2 justify-end">
        <button type="button" onclick="closeApproveModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700 transition">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-success text-white rounded-lg hover:bg-green-600 transition">
          Approve
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openApproveModal(userId, userName) {
  document.getElementById('userName').textContent = userName;
  document.getElementById('approveForm').action = '{{ route("admin.agents.requests.approve", ":id") }}'.replace(':id', userId);
  document.getElementById('approveModal').classList.remove('hidden');
  document.getElementById('approveModal').classList.add('flex');
}

function closeApproveModal() {
  document.getElementById('approveModal').classList.add('hidden');
  document.getElementById('approveModal').classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeApproveModal();
  }
});
</script>
</body>
</html>
@endsection

