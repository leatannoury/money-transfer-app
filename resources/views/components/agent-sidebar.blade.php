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

<aside class="w-64 flex-shrink-0 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark flex flex-col
               fixed top-0 left-0 h-screen">
  
  <!-- Top Section: Logo & Navigation -->
  <div class="p-6 flex flex-col flex-1 overflow-y-auto">
    <div class="flex items-center gap-3 mb-12 border-b border-border-light dark:border-border-dark pb-4">
      <div class="w-8 h-8 bg-primary rounded-full"></div>
      <span class="font-bold text-xl">Transferly</span>
    </div>

    <nav class="flex flex-col gap-2">
      <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="text-sm font-medium">Dashboard</span>
      </a>
      <a href="{{ route('agent.transactions') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
        <span class="material-symbols-outlined">receipt_long</span>
        <span class="text-sm font-medium">Transactions</span>
      </a>
      <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
        <span class="material-symbols-outlined">settings</span>
        <span class="text-sm font-medium">Settings</span>
      </a>
    </nav>
  </div>

  <!-- Bottom Section: User Info & Logout -->
  <div class="p-6 border-t border-border-light dark:border-border-dark flex items-center gap-3">
    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
    <div class="flex flex-col gap-2">
      <div>
        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
      </div>
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
