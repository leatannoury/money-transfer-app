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



<aside class="w-64 flex-shrink-0 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark flex flex-col
               fixed top-0 left-0 h-screen">
                     <div class="p-6 flex items-center gap-3 border-b border-border-light dark:border-border-dark">
        <div class="bg-black text-white p-2 rounded-lg">
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
              <a href="{{ route('admin.fees') }}" 
                class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 
                        hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined">payments</span>
                <span class="text-sm font-medium">Transaction Fees</span>
              </a>
          </li>

          <li>
            <a href="{{ route('admin.transactions.suspicious') }}" 
              class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 
                      hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">warning</span>
              <span class="text-sm font-medium">Suspicious Transfers</span>
            </a>
          </li>


          <li>
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">reviews</span>
              <span class="text-sm font-medium">Reviews</span>
            </a>
          </li>

                <li>
        <a href="{{ route('admin.chat.index') }}" 
          class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 
                  hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
          <span class="material-symbols-outlined">forum</span>
          <span class="text-sm font-medium">Support Chats</span>
        </a>
      </li>


      <li>
          <a href="{{ route('admin.reports.generate') }}" 
            class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 
                    hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">analytics</span>
              <span class="text-sm font-medium">Generate Reports</span>
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