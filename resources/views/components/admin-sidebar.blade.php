<aside class="w-64 flex-shrink-0 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark flex flex-col">
      <div class="p-6 flex items-center gap-3 border-b border-border-light dark:border-border-dark">
        <div class="bg-primary text-white p-2 rounded-lg">
          <span class="material-symbols-outlined">dashboard</span>
        </div>
        <h1 class="text-lg font-bold">Admin Panel</h1>
      </div>

      <nav class="flex-grow p-4">
        <ul class="flex flex-col gap-2">
          <li>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">grid_view</span>
              <span class="text-sm font-medium">Dashboard</span>
            </a>
          </li>
          <li>
            <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">group</span>
              <span class="text-sm font-semibold">Users</span>
            </a>
          </li>
          <li>
            <a href="{{ route('admin.agents') }}"  class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">support_agent</span>
              <span class="text-sm font-medium">Agents</span>
            </a>
          </li>
          <li>
            <a href="{{route('admin.transactions')}}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">receipt_long</span>
              <span class="text-sm font-medium">Transactions</span>
            </a>
          </li>
          <li>
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">reviews</span>
              <span class="text-sm font-medium">Reviews</span>
            </a>
          </li>
        </ul>
      </nav>

      <div class="flex items-center gap-3">
  <!-- Avatar -->
  <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>

  <!-- User Info and Logout -->
  <div class="flex flex-col gap-2">
    <div>
      <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
        <img src="{{ asset('images/logout-svgrepo-com.svg') }}" class="h-5 w-5" alt="Logout Icon" />

        <span class="text-sm font-medium">Log Out</span>
      </button>
    </form>
  </div>
</div>
    </aside>